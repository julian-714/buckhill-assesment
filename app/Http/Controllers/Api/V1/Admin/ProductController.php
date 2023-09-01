<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Exception;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Controllers\Api\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/v1/products",
     *      operationId="products-listing",
     *      tags={"Products"},
     *      summary="List all products",
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
     *         description="The number of products per page. Default is 10.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="The field to sort the products by (id, title, slug). Default is id.",
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
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $page = max(1, intval($request->query('page', '1')));
            $limit = max(1, intval($request->query('limit', '10')));
            $sortDesc = filter_var($request->query('desc', 'false'), FILTER_VALIDATE_BOOLEAN);
            $sortBy = $request->query('sortBy', 'id');

            // Build the query
            $products = Product::when(
                in_array($sortBy, ['id', 'title', 'slug']),
                function ($q) use ($sortBy, $sortDesc): void {
                    $q->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
                }
            )
                ->paginate($limit, ['*'], 'page', $page);

            return response()->json($products);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/product/create",
     *     tags={"Products"},
     *     summary="Store a new product",
     *     description="Creates a new product",
     *     operationId="storeProduct",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"category_uuid", "title", "price", "description", "metadata"},
     *                 @OA\Property(property="category_uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="brand", type="string"),
     *                 ),
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
     * Store a newly created product in the database.
     *
     * @param ProductStoreRequest $request The validated product store request.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function store(ProductStoreRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $validatedData['uuid'] = Str::orderedUuid();
            $validatedData['metadata'] = $request->metadata;

            $product = Product::create($validatedData);
            return $this->sendResponse(['uuid' => $product->uuid], 'Product created successfully');
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/product/{uuid}",
     *     operationId="updateProduct",
     *     tags={"Products"},
     *     summary="Update a product",
     *     description="Update an existing product",
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the product to update",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"category_uuid", "title", "price", "description", "metadata"},
     *                 @OA\Property(property="category_uuid", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="metadata", type="object",
     *                     @OA\Property(property="image", type="string"),
     *                     @OA\Property(property="brand", type="string"),
     *                 ),
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
     * @param ProductStoreRequest $request The validated product store request containing update data.
     * @param string $uuid The UUID of the product to update.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function update(ProductStoreRequest $request, string $uuid): \Illuminate\Http\JsonResponse
    {
        try {
            $product = Product::where('uuid', $uuid)->firstOrFail();
            $validatedData = $request->validated();
            $validatedData['metadata'] = $request->metadata;
            $product->update($validatedData);
            return $this->sendResponse($product->fresh(), 'Product updated successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Product not found', 404);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/product/{uuid}",
     *     summary="Fetch a single product by UUID",
     *     description="Retrieve details of a product by its UUID.",
     *     operationId="getProductByUuid",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the product",
     *         @OA\Schema(type="string", format="uuid")
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
     * )
     * Retrieve product data by UUID and return it as a JSON response.
     *
     * @param string $uuid The UUID of the product to retrieve.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing product data or an empty array if not found.
     */
    public function show(string $uuid): \Illuminate\Http\JsonResponse
    {
        $product = Product::where('uuid', $uuid)->first();
        return $this->sendResponse($product ?? [], 'Product fetch successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/product/{uuid}",
     *     summary="Delete a product by UUID",
     *     description="Delete a product by its UUID.",
     *     operationId="deleteProductByUuid",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the product",
     *         @OA\Schema(type="string", format="uuid")
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
     * Delete a product by its UUID.
     *
     * @param string $uuid The UUID of the product to delete.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response indicating success or failure.
     */
    public function delete(string $uuid): \Illuminate\Http\JsonResponse
    {
        try {
            $product = Product::where('uuid', $uuid)->firstOrFail();
            $product->delete();
            return $this->sendResponse([], 'Product deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->sendError('Product not found', 404);
        }
    }
}
