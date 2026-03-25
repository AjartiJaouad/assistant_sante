<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse; // Utilisation de notre structure JSON unifiée

    // US-01 : S'inscrire
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Compte créé avec succès', 201);
    }

    // US-02 : Se connecter
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->errorResponse(['auth' => 'Identifiants incorrects'], 'Non autorisé', 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        // Supprimer les anciens tokens (optionnel mais bonne pratique)
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->successResponse([
            'user' => $user,
            'token' => $token
        ], 'Connexion réussie');
    }

    // Révoquer le token actif
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse([], 'Déconnexion réussie');
    }

    // Infos de l'utilisateur connecté
    public function me(Request $request)
    {
        return $this->successResponse($request->user(), 'Profil récupéré');
    }
}
