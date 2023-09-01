<?php

namespace Logicrays\OrderStatusNotifier;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Logicrays\OrderStatusNotifier\Skeleton\SkeletonClass
 */
class OrderStatusNotifierFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'orderstatusnotifier';
    }
}
