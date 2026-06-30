<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // ── Helper: resolve a business the given user owns ───────────────────
    private function ownedBusiness($userId, $businessId): ?Business
    {
        $business = Business::find($businessId);
        if (!$business) return null;
        if ((int) $business->owner_id !== (int) $userId) return null;
        return $business;
    }

    // Parse the options payload (JSON string from multipart, or array)
    private function parseOptions(Request $request): array
    {
        $raw = $request->input('options');
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($raw) ? $raw : [];
    }

    // ═════════════════════════ OWNER ═════════════════════════

    // GET /api/user/businesses/products?user_id=&business_id=
    public function ownerProducts(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'business_id' => 'required|integer|exists:businesses,id',
        ]);

        $business = $this->ownedBusiness($request->user_id, $request->business_id);
        if (!$business) {
            return response()->json(['status' => 'error', 'message' => 'You are not the owner of this business.'], 403);
        }

        $products = Product::with('options')->where('business_id', $business->id)->orderByDesc('created_at')->get();

        return response()->json([
            'status' => 'success',
            'delivery_status' => $business->delivery_status,
            'data' => $products,
        ]);
    }

    // POST /api/user/businesses/products  (multipart)
    public function storeProduct(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'business_id' => 'required|integer|exists:businesses,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        $business = $this->ownedBusiness($request->user_id, $request->business_id);
        if (!$business) {
            return response()->json(['status' => 'error', 'message' => 'You are not the owner of this business.'], 403);
        }
        if ($business->delivery_status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'Home delivery is not approved for this business yet.'], 403);
        }

        $product = new Product();
        $product->business_id  = $business->id;
        $product->name         = $request->name;
        $product->description  = $request->description;
        $product->price        = $request->price;
        $product->is_available = $request->boolean('is_available', true);
        if ($request->hasFile('image')) {
            $product->image = $request->file('image')->store('user_products', 'public');
        }
        $product->save();

        foreach ($this->parseOptions($request) as $opt) {
            if (empty($opt['name'])) continue;
            $product->options()->create([
                'name'         => $opt['name'],
                'description'  => $opt['description'] ?? null,
                'price'        => isset($opt['price']) ? (float) $opt['price'] : 0,
                'is_available' => isset($opt['is_available']) ? (bool) $opt['is_available'] : true,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Product added successfully',
            'data'    => $product->load('options'),
        ]);
    }

    // POST /api/user/businesses/products/update  (multipart)
    public function updateProduct(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|integer',
            'product_id'  => 'required|integer|exists:products,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp',
        ]);

        $product = Product::find($request->product_id);
        $business = $product ? $this->ownedBusiness($request->user_id, $product->business_id) : null;
        if (!$product || !$business) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to edit this product.'], 403);
        }

        $product->name         = $request->name;
        $product->description  = $request->description;
        $product->price        = $request->price;
        $product->is_available = $request->boolean('is_available', true);
        if ($request->hasFile('image')) {
            if ($product->image && Storage::exists('public/' . $product->image)) {
                Storage::delete('public/' . $product->image);
            }
            $product->image = $request->file('image')->store('user_products', 'public');
        }
        $product->save();

        // Replace options
        $product->options()->delete();
        foreach ($this->parseOptions($request) as $opt) {
            if (empty($opt['name'])) continue;
            $product->options()->create([
                'name'         => $opt['name'],
                'description'  => $opt['description'] ?? null,
                'price'        => isset($opt['price']) ? (float) $opt['price'] : 0,
                'is_available' => isset($opt['is_available']) ? (bool) $opt['is_available'] : true,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Product updated successfully',
            'data'    => $product->load('options'),
        ]);
    }

    // POST /api/user/businesses/products/delete
    public function deleteProduct(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::find($request->product_id);
        $business = $product ? $this->ownedBusiness($request->user_id, $product->business_id) : null;
        if (!$product || !$business) {
            return response()->json(['status' => 'error', 'message' => 'You are not allowed to delete this product.'], 403);
        }

        if ($product->image && Storage::exists('public/' . $product->image)) {
            Storage::delete('public/' . $product->image);
        }
        $product->delete(); // options cascade

        return response()->json(['status' => 'success', 'message' => 'Product deleted successfully']);
    }

    // ═════════════════════════ PUBLIC ═════════════════════════

    // GET /api/businesses/{businessId}/products  — for the public order screen
    public function businessProducts(Request $request, $businessId)
    {
        $business = Business::find($businessId);
        if (!$business || $business->delivery_status !== 'approved') {
            return response()->json(['status' => 'error', 'message' => 'This business is not accepting orders.'], 404);
        }

        $products = Product::with(['options' => function ($q) {
                $q->where('is_available', true);
            }])
            ->where('business_id', $business->id)
            ->where('is_available', true)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => 'success',
            'business' => ['id' => $business->id, 'name' => $business->name],
            'data' => $products,
        ]);
    }

    // GET /api/shop/products — all orderable products with filters & sort
    public function shop(Request $request)
    {
        $perPage = (int) ($request->per_page ?? 15);
        $page    = (int) ($request->page ?? 1);

        $query = Product::with(['options' => function ($q) {
                $q->where('is_available', true);
            }, 'business:id,name,category_id,delivery_status'])
            ->where('is_available', true)
            ->whereHas('business', function ($q) {
                $q->where('delivery_status', 'approved');
            });

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            });
        }
        if ($request->category_id) {
            $query->whereHas('business', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        if (is_numeric($request->min_price)) $query->where('price', '>=', (float) $request->min_price);
        if (is_numeric($request->max_price)) $query->where('price', '<=', (float) $request->max_price);

        switch ($request->sort) {
            case 'price_low':  $query->orderBy('price', 'asc'); break;
            case 'price_high': $query->orderBy('price', 'desc'); break;
            default:           $query->orderByDesc('created_at'); break;
        }

        $total = (clone $query)->count();
        $products = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'status' => 'success',
            'data'   => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page'     => $perPage,
                'total'        => $total,
                'has_more'     => ($page * $perPage) < $total,
            ],
        ]);
    }
}
