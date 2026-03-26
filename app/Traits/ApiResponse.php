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
     * Compatibilité avec les anciens contrôleurs.
     */
    protected function successResponse($data, string $message = "Opération réussie", int $code = 200): JsonResponse
    {
        return $this->success($data, $message, $code);
    }

    /**
     * Réponse Erreur
     */
    protected function error($message, $code = 500, $errors = null): JsonResponse
    {
        if (! is_string($message)) {
            $legacyErrors = $message;
            $legacyCode = $errors;
            $message = is_string($code) ? $code : 'Une erreur est survenue';
            $code = is_int($legacyCode) ? $legacyCode : 500;
            $errors = $legacyErrors;
        }

        return response()->json([
            'success' => false,
            'errors' => $errors,
            'message' => $message
        ], $code);
    }

    /**
     * Compatibilité avec les anciens contrôleurs.
     */
    protected function errorResponse($errors, string $message = 'Une erreur est survenue', int $code = 500): JsonResponse
    {
        return $this->error($message, $code, $errors);
    }
}
