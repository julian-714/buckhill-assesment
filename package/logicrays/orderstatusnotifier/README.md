# Laravel Order status notifier



This Laravel package is designed to facilitate order status notifications by implementing Events and Listeners. It will enable you to send Microsoft Teams notification cards to a specified webhook endpoint whenever there is an update in the status of an order. The package will pass three mandatory parameters to the listener: order_uuid, new_status, and timestamp to create a meaningful notification card.

# Installation

You can install the package via composer:

```bash
composer require logicrays/orderstatusnotifier:dev-main
```

### Add your package's service provider to the config/app.php file:
```bash
Logicrays\OrderStatusNotifier\OrderStatusNotifierServiceProvider::class
```

### Publish the Configuration File
```bash
php artisan vendor:publish --tag=config --provider=Logicrays\\OrderStatusNotifier\\OrderStatusNotifierServiceProvider
```

### ENV
```env
TEAMS_WEBHOOK_URL=<YOUR_WEBHOOK_URL>
```

### Usage

```php
event(new OrderStatusNotifier($orderUuid, $newStatus, $timestamp))
```