<?php

namespace App\Http\Controllers\Api\V1\Main;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;

class BlogPostController extends BaseController
{
    /**
     * @OA\Get(
     *      path="/api/v1/main/blog",
     *      operationId="getBlogs",
     *      tags={"MainPage"},
     *      summary="Get list of blogs",
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
     *         description="The number of blogs per page. Default is 10.",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="The field to sort the blogs by (id, title). Default is id.",
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
        $page = max(1, intval($request->query('page', 1)));
        $limit = max(1, intval($request->query('limit', 10)));
        $sortBy = $request->query('sortBy', 'id');
        $sortDesc = filter_var($request->query('desc', 'false'), FILTER_VALIDATE_BOOLEAN);

        // Build the query
        $posts = Post::when(in_array($sortBy, ['id', 'title']), function ($q) use ($sortBy, $sortDesc): void {
            $q->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');
        })
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($posts);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/main/blog/{uuid}",
     *      operationId="getBlogByUUID",
     *      tags={"MainPage"},
     *      summary="Get blog information",
     *      description="Returns blog data",
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
     *      )
     * )
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function getBlog(Request $request): \Illuminate\Http\JsonResponse
    {
        $post = Post::where('uuid', $request->uuid)->first();
        return $this->sendResponse($post ?? [], 'Post fetch successfully');
    }
}
