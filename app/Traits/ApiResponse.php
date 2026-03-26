<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Réponse Success
     */
    protected function success($data, string $message = "Opération réussie", int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    /**
     * Réponse Erreur
     */
    protected function error(string $message, int $code, $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'errors' => $errors,
            'message' => $message
        ], $code);
    }
}
