<?php

namespace Logicrays\StripePayment\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use Stripe\StripeClient;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Logicrays\OrderStatusNotifier\OrderStatusNotifier;

class StripePaymentController extends Controller
{
    protected StripeClient $stripe;
    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe-payment.stripe_secret_key'));
    }

    /**
     * Process a payment for a given order.
     *
     * @param Request $request The incoming HTTP request
     * @param string $orderUuid The UUID of the order to process
     * @return \Illuminate\Http\Response
     */
    public function processPayment($orderUuid)
    {
        $order = $this->getOrderDetails($orderUuid);
        $totalAmount = $this->calculateTotalAmount($order);

        try {
            $checkoutSession = $this->createStripeCheckoutSession($totalAmount, $orderUuid);
            return $checkoutSession->url;
        } catch (\Stripe\Exception\CardException $e) {
            return $this->handleStripeException($e, 'Card');
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return $this->handleStripeException($e, 'Invalid request');
        } catch (\Stripe\Exception\AuthenticationException $e) {
            return $this->handleStripeException($e, 'Authentication');
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            return $this->handleStripeException($e, 'API connection');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return $this->handleStripeException($e, 'API');
        }
    }

    private function getOrderDetails($orderUuid)
    {
        return Order::where('uuid', $orderUuid)->firstOrFail();
    }

    private function calculateTotalAmount($order)
    {
        $subTotal = $order->amount + $order->delivery_fee;
        return $subTotal * 100;
    }

    private function createStripeCheckoutSession($totalAmount, $orderUuid)
    {
        $lineItems = $this->lineItem($totalAmount);
        $urls = [
            'success_url' => route('stripe.payment', ['order_uuid' => $orderUuid]) .
                '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.payment', ['order_uuid' => $orderUuid]) .
                '?session_id={CHECKOUT_SESSION_ID}',
        ];
        return $this->stripe->checkout->sessions->create([
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $urls['success_url'],
            'cancel_url' => $urls['cancel_url'],
        ]);
    }

    private function lineItem($totalAmount)
    {
        return [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Buy product',
                    ],
                    'unit_amount' => $totalAmount,
                ],
                'quantity' => 1,
            ],
        ];
    }
    /**
     * Update the order status.
     *
     * @param object $exception
     * @param string $errorType
     *
     * @return \Illuminate\Http\Response
     */
    private function handleStripeException(object $exception, string $errorType)
    {
        return ucfirst($errorType) . ' error: ' . $exception->getMessage();
    }

    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request
     */
    public function handleCallback(Request $request)
    {
        $checkout = $this->checkoutStripe($request->session_id);

        if ($checkout->payment_status === 'paid') {
            $orderStatus = $this->getOrderStatus();
            $this->updateOrder($request->order_uuid, $orderStatus->id);

            event(new OrderStatusNotifier($request->order_uuid, $orderStatus->title, Carbon::now()));

            return 'Your order has been placed and payment paid.';
        }
        return 'Your order has been placed and payment paid.';
    }

    private function getOrderStatus()
    {
        return OrderStatus::where('title', 'paid')->firstOrFail();
    }

    /**
     * Update the order status.
     *
     * @param string $orderStatusId
     * @param int $orderStatusId
     *
     * @return \Illuminate\Http\Response
     */
    private function updateOrder(string $orderUuid, int $orderStatusId)
    {
        return Order::where('uuid', $orderUuid)->update(['order_status_id' => $orderStatusId]);
    }

    private function checkoutStripe($sessionId)
    {
        return $this->stripe->checkout->sessions->retrieve($sessionId, []);
    }
}
