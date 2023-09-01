<?php

namespace App\Http\Controllers\Api\V1\Files;

use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class FileController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/v1/file/upload",
     *     summary="Upload a file",
     *     tags={"File"},
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     description="Upload File",
     *                     type="string", format="binary"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Page not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     ),
     *     security={
     *         {"apiAuth": {}}
     *     }
     * )
     * @return \Illuminate\Http\JsonResponse The JSON response containing product data or an empty array if not found.
     */
    public function uploadFile(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateFile($request);

        if ($validator->fails()) {
            return $this->sendResponse($validator->messages(), 'Validation error');
        }

        $fileUpload = $this->fileUpload($request);
        return $this->sendResponse($fileUpload, 'File uploaded successfully');
    }

    /**
     * Create a validator instance to validate the uploaded file.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing the uploaded file.
     *
     * @return \Illuminate\Validation\Validator The validator instance.
     */
    public function validateFile(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'file' => 'required|image',
        ]);
    }

    /**
     * Upload a file, store it in the specified location, and create a file record in the database.
     *
     * @param Request $request The HTTP request containing the uploaded file.
     *
     * @return \App\Models\File The created file record.
     */
    public function fileUpload(Request $request): File
    {
        $file = $request->file('file');
        $size = $file->getSize();
        $type = $file->getMimeType();
        $destinationPath = 'storage/pet-shop';
        $fileName = time() . '-' . $file->getClientOriginalName();
        $file->move($destinationPath, $fileName);
        $finalPath = $destinationPath . '/' . $fileName;
        $uuid = Str::orderedUuid();
        return File::create([
            'uuid' => $uuid, 'name' => $fileName,
            'path' => $finalPath, 'size' => $size, 'type' => $type,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/file/{uuid}",
     *      operationId="getFileByUUID",
     *      tags={"File"},
     *      summary="Read a file",
     *      description="Returns file data",
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
     * @return \Illuminate\Http\JsonResponse The JSON response containing product data or an empty array if not found.
     */
    public function getFile(Request $request): \Illuminate\Http\JsonResponse
    {
        $getFile = File::where('uuid', $request->uuid)->first();
        return $this->sendResponse($getFile ?? [], 'File retrieved successfully');
    }
}
