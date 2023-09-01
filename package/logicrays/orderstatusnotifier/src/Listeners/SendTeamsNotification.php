<?php

namespace Logicrays\OrderStatusNotifier\Listeners;

use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Logicrays\OrderStatusNotifier\OrderStatusNotifier;

class SendTeamsNotification implements ShouldQueue
{
    public function handle(OrderStatusNotifier $event): void
    {
        $webhookUrl = config('orderstatusnotifier.teams_webhook_url');

        $statusMessages = [
            'open' => 'Your order is placed',
            'paid' => 'Your order payment is successfully',
            'canceled' => 'Your order has been canceled',
            'shipped' => 'Your order has been shipped',
            'pending payment' => 'Your order payment is pending',
        ];

        $message = $statusMessages[$event->newStatus] ?? 'Order status updated';

        $notificationCard = json_encode([
            "message" => $message,
            "order_uuid" => $event->orderUuid,
            "new_status" => $event->newStatus,
            "timestamp" => $event->timestamp,
        ]);

        Http::post($webhookUrl, ['text' => $notificationCard]);
    }
}
