# Laravel stripe payment

This Laravel package is designed to make payment enabled projects by using the Stripe PHP library provides convenient access to the Stripe API from applications written in the PHP language. It includes a pre-defined set of classes for API resources that initialize themselves dynamically from API responses which makes it compatible with a wide range of versions of the Stripe API.

# Installation

You can install the package via composer:

```bash
composer require logicrays/stripe-payment:dev-main
```

### Add your package's service provider to the config/app.php file:

```bash
Logicrays\\StripePayment\\StripePaymentServiceProvider::class
```

### Publish the Configuration File

```bash
php artisan vendor:publish --tag=config --provider=Logicrays\\StripePayment\\StripePaymentServiceProvider
```

### ENV

```env
STRIPE_SECRET_KEY= <YOUR_STRIPE_SECRET_KEY>
STRIPE_PUBLISH_KEY= <YOUR_STRIPE_PUBLISH_KEY>
```

### Usage

```php
// 1. Include in your controller
use Stripe\StripeClient;

// 2. Create stripe object in your class
protected StripeClient $stripe;

public function __construct()
{
    $this->stripe = new StripeClient(config('stripe-payment.stripe_secret_key'));
}

// 3. Example of checkout API

$checkout_session = $this->stripe->checkout->sessions->create([
    'line_items' => [
        [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Product name',
                ],
                'unit_amount' => 500, //$5 you have to convert into cnent like: (5*100) = 500
            ],
            'quantity' => 1,
        ],
    ],
    'mode' => 'payment',
    'success_url' => route('stripe.payment', ['order_uuid' => '']) .
        '?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => route('stripe.payment', ['order_uuid' => '']) .
        '?session_id={CHECKOUT_SESSION_ID}',
]);
return $checkout_session->url;

// for more API visit stripe.com
```
