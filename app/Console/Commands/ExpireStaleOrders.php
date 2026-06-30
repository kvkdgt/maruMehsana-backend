<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Order;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

class ExpireStaleOrders extends Command
{
    protected $signature = 'orders:expire-stale';
    protected $description = 'Expire orders left in "requested" for 48h+ and auto-revoke delivery access after 3 strikes';

    private const HOURS = 48;
    private const STRIKE_LIMIT = 3;

    public function handle(): int
    {
        $cutoff = now()->subHours(self::HOURS);

        $staleOrders = Order::where('status', 'requested')
            ->where('created_at', '<', $cutoff)
            ->get();

        $affectedBusinessIds = [];

        foreach ($staleOrders as $order) {
            $order->status = 'expired';
            $order->save();

            // Strike the business for not responding in time
            Business::where('id', $order->business_id)->increment('delivery_strikes');
            $affectedBusinessIds[$order->business_id] = true;

            // Let the customer know
            PushNotificationService::sendToUser(
                $order->app_user_id,
                'Order Expired',
                "Order {$order->order_number} expired because the business did not respond within 48 hours.",
                ['type' => 'order', 'role' => 'customer', 'order_id' => $order->id]
            );
        }

        // Auto-revoke delivery for businesses that hit the strike limit
        $revoked = 0;
        $businesses = Business::whereIn('id', array_keys($affectedBusinessIds))
            ->where('delivery_status', 'approved')
            ->where('delivery_strikes', '>=', self::STRIKE_LIMIT)
            ->get();

        foreach ($businesses as $business) {
            $business->delivery_status = 'rejected';
            $business->delivery_reject_reason = 'Auto-revoked: ' . self::STRIKE_LIMIT . ' orders went unattended for 48+ hours.';
            $business->delivery_strikes = 0; // reset so a re-approval starts fresh
            $business->save();
            $revoked++;

            if ($business->owner_id) {
                PushNotificationService::sendToUser(
                    $business->owner_id,
                    'Delivery Access Revoked',
                    "Delivery for \"{$business->name}\" was revoked because " . self::STRIKE_LIMIT . " orders went unattended for 48+ hours. You can re-apply.",
                    ['type' => 'delivery', 'business_id' => $business->id]
                );
            }
        }

        $this->info("Expired {$staleOrders->count()} stale order(s); revoked delivery for {$revoked} business(es).");
        return self::SUCCESS;
    }
}
