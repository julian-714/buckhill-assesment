<?php

namespace Logicrays\OrderStatusNotifier;

use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class OrderStatusNotifier
{
    use Dispatchable, SerializesModels;

    public string $orderUuid;
    public string $newStatus;
    public Carbon $timestamp;

    public function __construct(string $orderUuid, string $newStatus, Carbon $timestamp)
    {
        $this->orderUuid = $orderUuid;
        $this->newStatus = $newStatus;
        $this->timestamp = $timestamp;
    }
}
