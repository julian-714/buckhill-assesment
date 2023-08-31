<?php

namespace App\Http\Controllers\Api\V1\Main;

use App\Models\Promotion;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;

class PromotionController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/v1/main/promotions",
     *      operationId="getPromotions",
     *      tags={"MainPage"},
     *      summary="Get list of  Promotions",
     *      description="Main Page API endpoint",
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
     *         description="The number of promotions per page. Default is 10.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="The field to sort the promotions by (id, title). Default is id.",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "title"})
     *     ),
     *     @OA\Parameter(
     *         name="desc",
     *         in="query",
     *         description="Sort in descending order (default: false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *    @OA\Parameter(
     *         name="valid",
     *         in="query",
     *         description="Promotions valid or not (default: false)",
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
     *      )
     *     )
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        // Retrieve parameters from the request
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $sortBy = $request->query('sortBy', 'id');
        $sortDesc = $request->query('desc', 'false');
        $valid = $request->query('valid', 'false');

        // Validate input
        $page = max(1, intval($page));
        $limit = max(1, intval($limit));
        $sortDesc = filter_var($sortDesc, FILTER_VALIDATE_BOOLEAN);
        $isValid = filter_var($valid, FILTER_VALIDATE_BOOLEAN);

        $todateDate = date('Y-m-d');

        // Build the query
        $getPromotion = Promotion::when($isValid === true, function ($q) use ($todateDate): void {
            $q->whereDate('metadata->valid_to', '>=', $todateDate);
        })->when(in_array($sortBy, ['id', 'title']), function ($q) use ($sortBy, $sortDesc): void {
            $q->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        })->paginate($limit, ['*'], 'page', $page);

        return response()->json($getPromotion);
    }
}
