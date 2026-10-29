<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $auth) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $login = $this->auth->login(
            $request->string('email')->toString(),
            $request->string('password')->toString(),
        );

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $login['token'],
            'user' => (new UserResource($login['user']))->resolve($request),
        ]);
    }

    public function logout(Request $request): Response
    {
        $this->auth->logout($request->user('sanctum'));

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
