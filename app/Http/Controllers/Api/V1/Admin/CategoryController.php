<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Api\BaseController;

class CategoryController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/v1/categories",
     *      operationId="categories-listing",
     *      tags={"Category"},
     *      summary="List all categories",
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
     *         description="The number of categories per page. Default is 10.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="The field to sort the category by (id, title, slug). Default is id.",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "title", "slug"})
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
     *      )
     *     )
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $page = max(1, intval($request->query('page', '1')));
            $limit = max(1, intval($request->query('limit', '10')));
            $sortDesc = filter_var($request->query('desc', 'false'), FILTER_VALIDATE_BOOLEAN);
            $sortBy = $request->query('sortBy', 'id');

            // Build the query
            $categories = Category::when(
                in_array($sortBy, ['id', 'title', 'slug']),
                function ($q) use ($sortBy, $sortDesc): void {
                    $q->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
                }
            )
                ->paginate($limit, ['*'], 'page', $page);

            return response()->json($categories);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }
}
