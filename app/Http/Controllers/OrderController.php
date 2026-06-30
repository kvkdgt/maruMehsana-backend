<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    private const ON_THE_WAY = ['requested', 'confirmed', 'dispatched'];
    private const COMPLETED  = ['delivered'];

    // ═════════════════════════ CUSTOMER ═════════════════════════

    // POST /api/orders  { user_id, business_id, items:[{product_id, product_option_id?, quantity}] }
    public function placeOrder(Request $request)
    {
        $request->validate([
            'user_id'                  => 'required|integer',
            'business_id'              => 'required|integer|exists:businesses,id',
            'items'                    => 'required|array|min:1',
            'items.*.product_id'       => 'required|integer',
            'items.*.product_option_id'=> 'nullable|integer',
            'items.*.quantity'         => 'required|integer|min:1',
        ]);

        $user = AppUser::find($request->user_id);
        if (!$user || !$user->is_login) {
            return response()->json(['status' => 'error', 'message' => 'Please login to place an order.'], 401);
        }
        if (empty($user->phone)) {
            return response()->json(['status' => 'need_mobile', 'message' => 'Please add your mobile number before ordering.'], 422);
        }

        $business = Business::find($request->business_id);
        if (!$business || $business->delivery_status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'This business is not accepting orders.'], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $user, $business) {
                $order = Order::create([
                    'order_number'    => 'TMP',
                    'app_user_id'     => $user->id,
                    'business_id'     => $business->id,
                    'status'          => 'requested',
                    'total_amount'    => 0,
                    'customer_name'   => $user->name,
                    'customer_mobile' => $user->phone,
                ]);
                $order->order_number = 'MM' . str_pad($order->id, 6, '0', STR_PAD_LEFT);

                $total = 0;
                foreach ($request->items as $item) {
                    $product = Product::where('id', $item['product_id'])
                        ->where('business_id', $business->id)
                        ->first();
                    if (!$product) {
                        throw new \RuntimeException('One of the products is not available.');
                    }

                    $optionName = null;
                    $unitPrice  = (float) $product->price;
                    $optionId   = null;

                    if (!empty($item['product_option_id'])) {
                        $option = ProductOption::where('id', $item['product_option_id'])
                            ->where('product_id', $product->id)->first();
                        if (!$option) {
                            throw new \RuntimeException('A selected option is not available.');
                        }
                        $optionId   = $option->id;
                        $optionName = $option->name;
                        $unitPrice  = (float) $option->price;
                    }

                    $qty  = (int) $item['quantity'];
                    $line = $unitPrice * $qty;
                    $total += $line;

                    $order->items()->create([
                        'product_id'        => $product->id,
                        'product_option_id' => $optionId,
                        'product_name'      => $product->name,
                        'option_name'       => $optionName,
                        'unit_price'        => $unitPrice,
                        'quantity'          => $qty,
                        'line_total'        => $line,
                    ]);
                }

                $order->total_amount = $total;
                $order->save();
                return $order;
            });
        } catch (\Throwable $e) {
            Log::error('Place order failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        // Notify the business owner (push works even if app is closed). Never breaks the order.
        if ($business->owner_id) {
            PushNotificationService::sendToUser(
                $business->owner_id,
                'New Order Received 🛎️',
                "Order {$order->order_number} • ₹" . number_format($order->total_amount, 0) . ' • ' . $order->items()->count() . ' item(s)',
                ['type' => 'order', 'role' => 'owner', 'order_id' => $order->id]
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Order placed successfully',
            'data'    => $order->load('items', 'business:id,name'),
        ]);
    }

    // GET /api/user/orders?user_id=&filter=on_the_way|completed|all
    public function myOrders(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);

        $query = Order::with(['items', 'business:id,name,thumbnail'])
            ->where('app_user_id', $request->user_id);

        if ($request->filter === 'on_the_way') {
            $query->whereIn('status', self::ON_THE_WAY);
        } elseif ($request->filter === 'completed') {
            $query->whereIn('status', array_merge(self::COMPLETED, ['cancelled', 'rejected']));
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderByDesc('created_at')->get(),
        ]);
    }

    // GET /api/orders/{id}?user_id=  (customer or owner of the business)
    public function orderDetail(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|integer']);

        $order = Order::with(['items', 'business:id,name,thumbnail,owner_id', 'customer:id,name,phone'])->find($id);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        $uid = (int) $request->user_id;
        $isCustomer = (int) $order->app_user_id === $uid;
        $isOwner    = (int) ($order->business->owner_id ?? 0) === $uid;
        if (!$isCustomer && !$isOwner) {
            return response()->json(['status' => 'error', 'message' => 'Not authorized to view this order.'], 403);
        }

        return response()->json(['status' => 'success', 'data' => $order, 'role' => $isOwner ? 'owner' : 'customer']);
    }

    // POST /api/orders/cancel  { user_id, order_id }  (customer, only while requested)
    public function cancelOrder(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|integer',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::find($request->order_id);
        if ((int) $order->app_user_id !== (int) $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'Not authorized.'], 403);
        }
        if ($order->status !== 'requested') {
            return response()->json(['status' => 'error', 'message' => 'This order can no longer be cancelled.'], 422);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json(['status' => 'success', 'message' => 'Order cancelled', 'data' => $order]);
    }

    // ═════════════════════════ OWNER ═════════════════════════

    // GET /api/user/orders/received?user_id=&filter=
    public function receivedOrders(Request $request)
    {
        $request->validate(['user_id' => 'required|integer']);

        $businessIds = Business::where('owner_id', $request->user_id)->pluck('id');

        $query = Order::with(['items', 'business:id,name', 'customer:id,name,phone'])
            ->whereIn('business_id', $businessIds);

        if ($request->filter === 'new') {
            $query->where('status', 'requested');
        } elseif ($request->filter === 'active') {
            $query->whereIn('status', ['confirmed', 'dispatched']);
        } elseif ($request->filter === 'completed') {
            $query->whereIn('status', ['delivered', 'cancelled', 'rejected']);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $query->orderByDesc('created_at')->get(),
        ]);
    }

    // POST /api/user/orders/update-status  { user_id, order_id, status, reject_reason? }
    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'user_id'  => 'required|integer',
            'order_id' => 'required|integer|exists:orders,id',
            'status'   => 'required|in:confirmed,dispatched,delivered,rejected',
            'reject_reason' => 'nullable|string|max:255',
        ]);

        $order = Order::with('business:id,name,owner_id')->find($request->order_id);
        if ((int) ($order->business->owner_id ?? 0) !== (int) $request->user_id) {
            return response()->json(['status' => 'error', 'message' => 'You do not own this order\'s business.'], 403);
        }

        $allowed = [
            'requested'  => ['confirmed', 'rejected'],
            'confirmed'  => ['dispatched'],
            'dispatched' => ['delivered'],
        ];
        $current = $order->status;
        if (!isset($allowed[$current]) || !in_array($request->status, $allowed[$current])) {
            return response()->json([
                'status' => 'error',
                'message' => "Cannot change a {$current} order to {$request->status}.",
            ], 422);
        }

        $order->status = $request->status;
        if ($request->status === 'rejected') {
            $order->reject_reason = $request->reject_reason;
        }
        $order->save();

        // Notify the customer of the status change.
        $labels = [
            'confirmed'  => ['Order Confirmed ✅', 'The business will call you for payment collection.'],
            'dispatched' => ['Order Dispatched 🚚', 'Your order is on the way.'],
            'delivered'  => ['Order Delivered 🎉', 'Your order has been delivered. Thank you!'],
            'rejected'   => ['Order Rejected', $request->reject_reason ?: 'Sorry, the business could not accept your order.'],
        ];
        [$title, $body] = $labels[$request->status];
        PushNotificationService::sendToUser(
            $order->app_user_id,
            $title,
            "Order {$order->order_number}: {$body}",
            ['type' => 'order', 'role' => 'customer', 'order_id' => $order->id]
        );

        return response()->json(['status' => 'success', 'message' => 'Order status updated', 'data' => $order]);
    }
}
