<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\JwtToken;
use Illuminate\Http\Request;
use App\Events\UserAuthAttempt;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\BaseController;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     *      path="/api/v1/admin/login",
     *      operationId="adminLogin",
     *      tags={"Admin"},
     *      summary="Admin login",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", format="email", example="admin@buckhill.co.uk"),
     *                 @OA\Property(property="password", type="string", example="admin"),
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
     *      )
     *     )
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validateLogin($request);
        $token = $this->processLogin($request);
        // If Token then login success
        if ($token) {
            $this->callLoginEvent($token);
            return $this->sendResponse(['token' => $token], 'Login successfully');
        }
        return $this->sendError('Failed to authenticate user', 422);
    }

    /**
     * Validate user login credentials in the provided request.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user login data.
     */
    public function validateLogin(Request $request): void
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Proccess Login and generate token
     * @param string $token The authentication token associated with the login attempt.
     */
    public function callLoginEvent(string $token): void
    {
        event(new UserAuthAttempt(Auth::user(), $token));
    }

    /**
     * Process Login and generate token
     * @return string<string|null>
     */
    public function processLogin(Request $request): string
    {
        return Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'is_admin' => 1,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/logout",
     *     operationId="adminLogout",
     *     summary="Admin logout",
     *     tags={"Admin"},
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
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function logout(): \Illuminate\Http\JsonResponse
    {
        if (Auth::check()) {
            $this->destroyJwtToken();
            $this->authLogout();
        }
        return $this->sendResponse([], 'Logout successfully');
    }

    /**
     * Delete the JWT tokens associated with the authenticated user.
     */
    public function destroyJwtToken(): void
    {
        JwtToken::where('user_id', auth()->user()->id)->delete();
    }

    /**
     * Log the authenticated user out of the application.
     */
    public function authLogout(): void
    {
        Auth::logout();
    }
}
