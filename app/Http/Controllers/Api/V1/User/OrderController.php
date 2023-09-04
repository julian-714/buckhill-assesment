<?php

namespace App\Http\Controllers\Api\V1\User;

use stdClass;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\OrderStatus;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\ValidatePaymentId;
use Illuminate\Support\Facades\Log;
use App\Rules\ValidateOrderStatusId;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Logicrays\OrderStatusNotifier\OrderStatusNotifier;
use Logicrays\StripePayment\Controllers\StripePaymentController;

class OrderController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/v1/orders",
     *      operationId="orders-listing",
     *      tags={"Orders"},
     *      summary="List all orders",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="The page number for pagination. Default is 1.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="The number of orders per page. Default is 10.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="The field to sort the orders by (id). Default is id.",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id"})
     *     ),
     *      @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort in descending order (default: false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Page not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      ),
     *     security={
     *         {"apiAuth": {}}
     *     }
     *     )
     */
    public function index(Request $request)
    {
        // Retrieve parameters from the query request and validate input
        $page = $this->getPage($request);
        $limit = $this->getLimit($request);
        $sortBy = $this->getSortBy($request);
        $sortDesc = $this->getSortDesc($request);

        $query = $this->buildOrderQuery();

        $newQuery = $this->loadEloquentRelations($query, $sortBy, $sortDesc);

        $orders = $newQuery->paginate($limit, ['*'], 'page', $page);

        return response()->json($orders);
    }

    /**
     * Retrieve and validate the 'page' query parameter from the given Request object.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the 'page' query parameter.
     * @return int  The validated 'page' parameter as an integer, with a minimum value of 1.
     */
    private function getPage(Request $request): int
    {
        return max(1, intval($request->query('page', 1)));
    }

    /**
     * Retrieve and validate the 'limit' query parameter from the given Request object.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the 'limit' query parameter.
     * @return int  The validated 'limit' parameter as an integer, with a minimum value of 1.
     */
    private function getLimit(Request $request): int
    {
        return max(1, intval($request->query('limit', 10)));
    }

    /**
     * Retrieve the 'sortBy' query parameter from the given Request object.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the 'sortBy' query parameter.
     * @return string  The value of the 'sortBy' query parameter, or 'id' if not found.
     */
    private function getSortBy(Request $request): string
    {
        return $request->query('sortBy', 'id');
    }

    /**
     * Retrieve the 'sortDesc' query parameter from the given Request object.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the 'sortDesc' query parameter.
     * @return string  The value of the 'sortDesc' query parameter, or 'id' if not found.
     */
    private function getSortDesc(Request $request): string
    {
        return filter_var($request->query('desc', 'false'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Build and return a query for retrieving orders with related user, order status, and payment information.
     *
     * @return \Illuminate\Database\Eloquent\Builder  The query builder instance with eager loading.
     */
    private function buildOrderQuery(): Builder
    {
        $query = Order::query();
        return $query->with(['user', 'orderStatus', 'payment']);
    }

    /**
     * Load Eloquent relations and apply filters based on user's role, sorting, and desc.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query     The query builder instance to be modified.
     * @param  string                                $sortBy    The column name to sort by.
     * @param  bool                                  $sortDesc  Whether to sort in descending order.
     * @return \Illuminate\Database\Eloquent\Builder           The modified query builder instance.
     */
    private function loadEloquentRelations(Builder $query, string $sortBy, bool $sortDesc): Builder
    {
        if (auth()->user()->is_admin === 0) {
            $query->where('user_id', auth()->user()->id);
        }
        if (in_array($sortBy, ['id'])) {
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        }
        return $query;
    }

    /**
     * Store a new order.
     *
     * @OA\Post(
     *     path="/api/v1/order/create",
     *     summary="Create a new order",
     *     tags={"Orders"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"order_status_uuid", "payment_uuid", "products", "address"},
     *             @OA\Property(property="order_status_uuid", type="string", format="uuid",
     *                              description="The UUID of the order status."),
     *             @OA\Property(property="payment_uuid", type="string", format="uuid",
     *                              description="The UUID of the payment."),
     *                 @OA\Property(property="products", type="array", @OA\Items(
     *                     type="object",
     *                     required={"product_uuid", "quantity"},
     *                     @OA\Property(property="product_uuid", type="string", format="uuid",
     *                                      description="The UUID of a product."),
     *                     @OA\Property(property="quantity", type="integer",
     *                                      description="The quantity of the product."),
     *                 ), description="An array of products with their UUIDs and quantities."),
     *             @OA\Property(property="address", type="object", @OA\Property(
     *                 property="billing", type="string", description="The billing address."),
     *                 @OA\Property(property="shipping", type="string", description="The shipping address.")
     *             ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Page not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      ),
     *     security={
     *         {"apiAuth": {}}
     *     }
     * )
     */
    public function store(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 'Validation error');
        }
        try {
            $productsArr = $this->prepareProducts($request->products);
            if (is_string($productsArr)) {
                return $this->sendError('Product not found', 404);
            }
            $totalAmount = $this->calculateTotalAmount($productsArr);
            $oStatus = $this->getOrderStatus($request->order_status_uuid);
            $newOrder = $this->createOrder($request, $oStatus, $productsArr, $totalAmount);

            $this->notifyOrderStatus($newOrder->uuid, $oStatus->title);
            $payMethodCreditCard = $this->paymentMethod($request->payment_uuid);
            if ($payMethodCreditCard === true) {
                $paymentLink = $this->stripePayment($newOrder->uuid);
                return $this->sendResponse([
                    'order_uuid' => $newOrder->uuid, 'payment_url' => $paymentLink,
                ], 'Order placed successfully');
            }
            return $this->sendResponse([
                'order_uuid' => $newOrder->uuid,
            ], 'Order placed successfully');
        } catch (\Exception $e) {
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Initiate a Stripe payment process for the specified order UUID.
     *
     * @param  string  $uuid  The UUID of the order for which the Stripe payment is initiated.
     * @return mixed          The result of the Stripe payment processing.
     */
    private function stripePayment($uuid)
    {
        $paymentProcessor = new StripePaymentController();
        return $paymentProcessor->processPayment($uuid);
    }
    /**
     * @OA\Put(
     *     path="/api/v1/order/{uuid}",
     *     operationId="updateOrder",
     *     tags={"Orders"},
     *     summary="Update a order",
     *     description="Update an existing order",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the order to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"order_status_uuid", "payment_uuid", "products", "address"},
     *             @OA\Property(property="order_status_uuid", type="string", format="uuid",
     *              description="The UUID of the order status."),
     *             @OA\Property(property="payment_uuid", type="string", format="uuid",
     *              description="The UUID of the payment."),
     *                 @OA\Property(property="products", type="array", @OA\Items(
     *                     type="object",
     *                     required={"product_uuid", "quantity"},
     *                     @OA\Property(property="product_uuid", type="string", format="uuid",
     *                                      description="The UUID of a product."),
     *                     @OA\Property(property="quantity", type="integer",
     *                                  description="The quantity of the product."),
     *                 ), description="An array of products with their UUIDs and quantities."),
     *             @OA\Property(property="address", type="object", @OA\Property(
     *                 property="billing", type="string", description="The billing address."),
     *                 @OA\Property(property="shipping", type="string", description="The shipping address.")
     *             ),
     *             ),
     *         ),
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Page not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      ),
     *     security={
     *         {"apiAuth": {}}
     *     }
     * )
     */
    public function update(Request $request, $uuid)
    {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return $this->sendResponse($validator->errors(), 'Validation error');
        }

        try {
            $productsArr = $this->prepareProducts($request->products);
            $totalAmount = $this->calculateTotalAmount($productsArr);

            $deliveryFee = $totalAmount < 500 ? 15 : 0;
            $orderStatus = $this->getOrderStatus($request->order_status_uuid);
            $paymentId = $this->getPaymentId($request->payment_uuid);

            $this->updateOrder($request, $orderStatus, $paymentId, $productsArr, $totalAmount, $deliveryFee, $uuid);

            $this->notifyOrderStatus($uuid, $orderStatus->title);

            return $this->sendResponse($uuid, 'Order updated successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Validate the incoming request data for creating or updating an order.
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request object containing the data to validate.
     * @return \Illuminate\Contracts\Validation\Validator  The Validator instance with the defined rules.
     */
    private function validateRequest(Request $request): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($request->all(), [
            'order_status_uuid' => ['required', 'string', new ValidateOrderStatusId($request->order_status_uuid ?? "")],
            'payment_uuid' => ['required', new ValidatePaymentId($request->payment_uuid ?? "")],
            'products' => ['required', 'array'],
            'address.billing' => ['required', 'string'],
            'address.shipping' => ['required', 'string'],
        ]);
    }

    /**
     * Prepare an array of product details based on the provided product data.
     *
     * @param  array  $products  An array of product data, each containing 'product_uuid' and 'quantity'.
     */
    private function prepareProducts(array $products): array|string
    {
        $productsArr = [];
        foreach ($products as $pro) {
            $product = $this->getProduct($pro['product_uuid']);
            if (is_string($product)) {
                return $product;
            }
            $newProduct = new stdClass();
            $newProduct->uuid = $pro['product_uuid'];
            $newProduct->quantity = $pro['quantity'];
            $newProduct->price = $product->price;
            $newProduct->product = $product->title;

            $productsArr[] = $newProduct;
        }
        return $productsArr;
    }

    /**
     * Retrieve product information based on the provided product ID.
     *
     * @param  int|string  $productId  The ID or UUID of the product to retrieve.
     * @return \App\Models\Product|string  The product information if found, or "Product not found" if not found.
     */
    private function getProduct(int|string $productId): Product|string
    {
        $product = Product::getPrice($productId);
        return $product ?? "Product not found";
    }

    /**
     * Calculate the total amount for a list of products based on their quantity and price.
     *
     * @param  array  $productsArr  An array of product objects, each containing quantity and price attributes.
     * @return float  The calculated total amount as a floating-point number.
     */
    private function calculateTotalAmount(array $productsArr): float
    {
        return array_reduce($productsArr, function ($carry, $product) {
            return $carry + ($product->quantity * $product->price);
        }, 0);
    }

    /**
     * Retrieve an OrderStatus model by its UUID.
     *
     * @param  string  $orderStatusUuid  The UUID of the OrderStatus to retrieve.
     * @return \App\Models\OrderStatus|null  The found OrderStatus model or null if not found.
     */
    private function getOrderStatus(string $orderStatusUuid): ?\App\Models\OrderStatus
    {
        return OrderStatus::where('uuid', $orderStatusUuid)->first();
    }

    /**
     * Retrieve an Payment model by its UUID.
     *
     * @param  string  $paymentUuid  The UUID of the Payment to retrieve.
     * @return int|null  The found Payment id or null if not found.
     */
    private function getPaymentId(string $paymentUuid): ?int
    {
        return Payment::where('uuid', $paymentUuid)->pluck('id')[0];
    }

    /**
     * Create a new order record in the database.
     *
     * @param  \Illuminate\Http\Request  $request      The HTTP request object containing additional order data.
     * @param  \App\Models\OrderStatus  $orderStatus  The OrderStatus model representing the order's status.
     * @param  array  $productsArr                    An array containing product details.
     * @param  float  $totalAmount                   The total order amount.
     * @return \App\Models\Order                    The created Order model instance.
     */
    private function createOrder(
        Request $request,
        OrderStatus $orderStatus,
        array $productsArr,
        float $totalAmount
    ): Order {
        return Order::create([
            'uuid' => Str::orderedUuid(),
            'user_id' => auth()->user()->id,
            'order_status_id' => $orderStatus->id,
            'payment_id' => $this->getPaymentId($request->payment_uuid),
            'products' => $productsArr,
            'address' => $request->address,
            'delivery_fee' => $totalAmount < 500 ? 15 : 0,
            'amount' => $totalAmount,
        ]);
    }

    /**
     * Notify request to ms team notification package about a change in order status.
     *
     * @param  string  $uuid    The UUID of the order for which the status is being updated.
     * @param  string  $title   The new order status title or description.
     */
    private function notifyOrderStatus(string $uuid, string $title): void
    {
        event(new OrderStatusNotifier($uuid, $title, Carbon::now()));
    }

    /**
     * Update an existing order record in the database.
     *
     * @param  \Illuminate\Http\Request  $request      The HTTP request object containing updated order data.
     * @param  \App\Models\OrderStatus  $orderStatus  The OrderStatus model representing the updated order status.
     * @param  int  $paymentId                        The ID of the updated payment method.
     * @param  array  $productsArr                    An array containing updated product details.
     * @param  float  $totalAmount                   The updated total order amount.
     * @param  float  $deliveryFee                   The updated delivery fee for the order.
     * @param  string  $uuid                         The UUID of the order to be updated.
     * @return int                                   The number of updated rows in the database.
     */
    private function updateOrder(
        Request $request,
        \App\Models\OrderStatus $orderStatus,
        int $paymentId,
        array $productsArr,
        float $totalAmount,
        float $deliveryFee,
        string $uuid
    ): int {
        return Order::where('uuid', '=', $uuid)->update([
            'user_id' => auth()->user()->id,
            'order_status_id' => $orderStatus->id,
            'payment_id' => $paymentId,
            'products' => $productsArr,
            'address' => $request->address,
            'delivery_fee' => $deliveryFee,
            'amount' => $totalAmount,
        ]);
    }

    /**
     * Check payment method for payment link generate.
     *
     * @param  string  $uuid The UUID of the payment.
     */
    private function paymentMethod(string $paymentUuid)
    {
        $paymentMethod = Payment::where('uuid', $paymentUuid)->first();
        if ($paymentMethod->type === 'credit_card') {
            return true;
        }
        return false;
    }
}
