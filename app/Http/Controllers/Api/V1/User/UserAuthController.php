<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\User;
use App\Models\JwtToken;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\UserAuthAttempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseController;

class UserAuthController extends BaseController
{
    /**
     * @OA\Post(
     *      path="/api/v1/user/login",
     *      operationId="userLogin",
     *      tags={"User"},
     *      summary="User login",
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(
     *                     property="email",
     *                     description="Enter Email",
     *                     type="string",
     *                 ),
     *                  @OA\Property(
     *                     property="password",
     *                     description="Enter Password",
     *                     type="string",
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
     *      )
     *     )
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateLogin($request);
        if ($validator->fails()) {
            return $this->sendResponse($validator->messages(), 'Validation error');
        }
        try {
            $token = $this->processLogin($request);
            if (!$token) {
                return $this->sendError('Failed to authenticate user', 422);
            }
            $this->callUserLoginEvent($token);
            return $this->sendResponse(['token' => $token], 'Login successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Create a validator instance to validate user login credentials.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user login data.
     *
     * @return \Illuminate\Validation\Validator The validator instance.
     */
    public function validateLogin(\Illuminate\Http\Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
    }

    /**
     * Attempt to log in a user with the provided email and password, excluding admin users.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user login data.
     *
     * @return string True if the login attempt is successful, false otherwise.
     */
    public function processLogin(Request $request): string
    {
        return Auth::attempt(['email' => $request->email, 'password' => $request->password, 'is_admin' => 0]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/user/register",
     *      operationId="userRegister",
     *      tags={"User"},
     *      summary="User register",
     *    @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                required={"first_name", "last_name","email","password",
     *                         "password_confirmation","address","phone_number"},
     *                @OA\Property(
     *                     property="first_name",
     *                     description="First Name",
     *                     type="string",
     *                     nullable=false,
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     description="First Name",
     *                     type="string",
     *                     nullable=false,
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="Enter Email",
     *                     type="string",
     *                     nullable=false,
     *                 ),
     *                  @OA\Property(
     *                     property="password",
     *                     description="Enter Password",
     *                     type="string",
     *                     nullable=false,
     *                 ),
     *                  @OA\Property(
     *                     property="password_confirmation",
     *                     description="Re Enter Password",
     *                     type="string",
     *                     nullable=false,
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     description="Image UUID",
     *                     type="string",
     *                 ),
     *               @OA\Property(
     *                     property="address",
     *                     description="Enter Address",
     *                     type="string",
     *                     nullable=false,
     *                 ),
     *                @OA\Property(
     *                     property="phone_number",
     *                     description="Phone Number",
     *                     type="integer",
     *                 ),
     *               @OA\Property(
     *                     property="is_marketing",
     *                     description="Is Marketing",
     *                     type="integer",
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
     *      )
     *     )
     * @return \Illuminate\Http\JsonResponse The JSON response with the success status, data, and message.
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = $this->validateRegisterForm($request);
        if ($validator->fails()) {
            return $this->sendResponse($validator->messages(), 'Validation error');
        }
        try {
            $token = $this->registerUserAndCreateToken($request);
            return $this->sendResponse(['token' => $token], 'User created successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError('Internal server error', 500);
        }
    }

    /**
     * Register a user, create an authentication token, and trigger a user login event.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user registration data.
     *
     * @return string The authentication token for the registered user.
     */
    public function registerUserAndCreateToken(Request $request): string
    {
        $user = $this->createUser($request);
        $token = Auth::login($user);
        $this->callUserLoginEvent($token);
        return $token;
    }

    /**
     * Create a validator instance to validate user registration data.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user registration data.
     *
     * @return \Illuminate\Validation\Validator The validator instance.
     */
    public function validateRegisterForm(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'address' => 'required|string|max:255',
            'phone_number' => 'required|numeric',
        ]);
    }

    /**
     * Create a new user record with the provided user registration data.
     *
     * @param \Illuminate\Http\Request $request The HTTP request containing user registration data.
     *
     * @return \App\Models\User The newly created user instance.
     */
    public function createUser(Request $request): \App\Models\User
    {
        return User::create([
            'uuid' => Str::orderedUuid(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => Str::orderedUuid(),
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'is_marketing' => $request->is_marketing ?? '0',
        ]);
    }

    /**
     * Trigger a custom event when a user attempts to log in and provide the authentication token.
     *
     * @param $token The authentication token associated with the login attempt.
     */
    public function callUserLoginEvent($token): void
    {
        event(new UserAuthAttempt(Auth::user(), $token));
    }

    /**
     * @OA\Get(
     *     path="/api/v1/user/logout",
     *     operationId="userLogout",
     *     summary="User logout",
     *     tags={"User"},
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
