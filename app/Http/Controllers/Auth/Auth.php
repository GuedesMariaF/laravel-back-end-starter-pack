<?php

namespace App\Http\Controllers\Auth;

use App\Builder\ReturnApi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Dedoc\Scramble\Attributes\Response;
use Illuminate\Http\JsonResponse;

class Auth extends Controller
{
    public function __construct(public AuthService $authService) {}

    #[Response(401, 'Usuário ou senha inválido', type: 'array{error: true, message: string, data: array<never>}', examples: [['error' => true, 'message' => 'Usuário ou senha inválido', 'data' => []]])]
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return ReturnApi::success($data, 'Usuário logado com sucesso');
    }

    public function me(): JsonResponse
    {
        $data = $this->authService->me();

        return ReturnApi::success($data, 'Dados do usuário');
    }

    public function refreshToken(): JsonResponse
    {
        $data = $this->authService->refreshToken();

        return ReturnApi::success($data, 'Token atualizado com sucesso');
    }
}
