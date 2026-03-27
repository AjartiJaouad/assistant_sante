<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: '/api/register',
        summary: 'Creer un compte utilisateur',
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 201, description: 'Compte cree'),
            new OA\Response(response: 422, description: 'Erreur de validation'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Compte créé avec succès', 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Obtenir un token Sanctum',
        tags: ['Authentification'],
        responses: [
            new OA\Response(response: 200, description: 'Connexion reussie'),
            new OA\Response(response: 401, description: 'Non autorise'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return $this->errorResponse(['auth' => ['Identifiants incorrects']], 'Non autorisé', 401);
        }

        $user = User::where('email', $credentials['email'])->firstOrFail();
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'Connexion réussie');
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Revoquer le token actif',
        tags: ['Authentification'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Deconnexion reussie'),
            new OA\Response(response: 401, description: 'Non autorise'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->successResponse([], 'Déconnexion réussie');
    }

    #[OA\Get(
        path: '/api/me',
        summary: 'Recuperer l utilisateur connecte',
        tags: ['Authentification'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Profil recupere'),
            new OA\Response(response: 401, description: 'Non autorise'),
        ]
    )]
    public function me(Request $request): JsonResponse
    {
        return $this->successResponse($request->user(), 'Profil récupéré');
    }
}
