<?php

namespace App\Http\Controllers\Api\V1\Payment;

use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/v1/payments",
     *      operationId="getAllPayments",
     *      tags={"Payments"},
     *      summary="payments listing",
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
     *         description="The number of payments per page. Default is 10.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="The field to sort the payments by (id, type). Default is id.",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "type"})
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort in descending order (default: false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter payments by type (partial match)",
     *         required=false,
     *         @OA\Schema(type="string")
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
    public function getAllPayments(Request $request)
    {
        try {
            $page = max(1, intval($request->query('page', '1')));
            $limit = max(1, intval($request->query('limit', '10')));
            $sortDesc = filter_var($request->query('desc', 'false'), FILTER_VALIDATE_BOOLEAN);
            $sortBy = $request->query('sortBy', 'id');
            $typeFilter = $request->query('type', '');

            // Build the query
            $payments = Payment::when($typeFilter, function ($q) use ($typeFilter): void {
                $q->where('type', 'like', '%' . $typeFilter . '%');
            })
                ->when(in_array($sortBy, ['id', 'type']), function ($q) use ($sortBy, $sortDesc): void {
                    $q->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
                })
                ->paginate($limit, ['*'], 'page', $page);

            return response()->json($payments);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/payment/create",
     *     summary="Process a payment",
     *     description="Process a payment based on the selected payment type and details.",
     *     operationId="processPayment",
     *     tags={"Payments"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"payment_type", "details"},
     *             @OA\Property(
     *                 property="payment_type",
     *                 type="string",
     *                 enum={"credit_card", "cash_on_delivery", "bank_transfer"},
     *                 description="Type of payment (credit_card, cash_on_delivery, bank_transfer)."
     *             ),
     *             @OA\Property(
     *                 property="details",
     *                 type="object",
     *                 description="Payment details in JSON format."
     *             )
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
    public function processPayment(Request $request)
    {
        //Validate the incoming request
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->sendResponse($validator->messages(), 'Validation error');
        }
        try {
            // Process the payment based on the selected payment type and details
            $payment = $this->storePayment($request);
            return $this->sendResponse(['uuid' => $payment->uuid], 'Payment processed successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Store a new payment record based on the provided payment data.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing payment information.
     *
     * @return \App\Models\Payment The newly created payment record.
     */
    public function storePayment(Request $request): Payment
    {
        return Payment::create([
            'uuid' => Str::orderedUuid(),
            'type' => $request->payment_type,
            'details' => $request->details,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/payment/{uuid}",
     *      operationId="getPaymentByUUID",
     *      tags={"Payments"},
     *      summary="Get payment information",
     *      description="Returns payment data",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="UUID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
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
    public function getPayment(Request $request)
    {
        $payment = $this->getPaymentByUuid($request->uuid);
        return $this->sendResponse($payment ?? [], "Payment retrieved successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/v1/payment/{uuid}",
     *     summary="Update a payment",
     *     description="Update a payment based on the selected payment type and details.",
     *     operationId="updatePayment",
     *     tags={"Payments"},
     *      @OA\Parameter(
     *          name="uuid",
     *          description="UUID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"payment_type", "details"},
     *             @OA\Property(
     *                 property="payment_type",
     *                 type="string",
     *                 enum={"credit_card", "cash_on_delivery", "bank_transfer"},
     *                 description="Type of payment (credit_card, cash_on_delivery, bank_transfer)."
     *             ),
     *             @OA\Property(
     *                 property="details",
     *                 type="object",
     *                 description="Payment details in JSON format."
     *             )
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
    public function updatePayment(Request $request)
    {
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->sendResponse($validator->messages(), 'Validation error');
        }
        $payment = $this->getPaymentByUuid($request->uuid);
        if ($payment !== null) {
            $this->updatePaymentDetails($payment, $request->payment_type, $request->details);
            return $this->sendResponse([], 'Payment updated successfully');
        }
        return $this->sendError('Payment not found', 404);
    }

    /**
     * Retrieve a payment record by its UUID.
     *
     * @param string $uuid The UUID of the payment record to retrieve.
     *
     * @return \App\Models\Payment|null The payment record or null if not found.
     */
    public function getPaymentByUuid($uuid)
    {
        return Payment::where('uuid', $uuid)->first();
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/payment/{uuid}",
     *      operationId="deletePaymentByUUID",
     *      tags={"Payments"},
     *      summary="Delete payment record",
     *      description="Delete payment data",
     *      @OA\Parameter(
     *          name="uuid",
     *          description="UUID",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
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
    public function deletePayment(Request $request)
    {
        try {
            $getPayment = Payment::where('uuid', $request->uuid)->firstOrFail();
            $getPayment->delete();
            return $this->sendResponse([], 'Payment deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Payment not found', 404);
        }
    }

    /**
     * Create a validator instance to validate the provided request data.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing payment information.
     *
     * @return \Illuminate\Validation\Validator The validator instance.
     */
    private function validateRequest(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'payment_type' => 'required|in:credit_card,cash_on_delivery,bank_transfer',
                'details' => 'required|json',
            ]
        );
    }

    /**
     * Update the payment type and details for a given payment record.
     *
     * @param \App\Models\Payment $payment The payment record to update.
     * @param string $paymentType The updated payment type.
     * @param string $details The updated payment details in JSON format.
     *
     * @return bool True if the update was successful, false otherwise.
     */
    private function updatePaymentDetails($payment, $paymentType, $details): bool
    {
        return $payment->update([
            'type' => $paymentType,
            'details' => $details,
        ]);
    }
}
