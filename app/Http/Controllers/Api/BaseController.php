<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    /**
     * Send a JSON response with a success status, result data, and a message.
     *
     * @param mixed $result The data to include in the response.
     * @param string $message A message to describe the response.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function sendResponse(mixed $result, string $message): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
            'status' => 200,
        ];
        return response()->json($response, 200);
    }

    /**
     * Send a JSON response with an error status, an error message, and an optional custom status code.
     *
     * @param string $error The error message to include in the response.
     * @param int $code (Optional) The HTTP status code for the response (default: 404).
     *
     * @return \Illuminate\Http\JsonResponse The JSON response with the error status, message, and status code.
     */
    public function sendError(string $error, int $code = 404): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'data' => [],
            'message' => $error,
            'status' => $code,
        ];
        return response()->json($response, $code);
    }
}
