<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $renderJsonError = static function (
            Request $request,
            string $message,
            int $status,
            array|null $errors = null
        ): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'errors' => $errors,
                'message' => $message,
            ], $status);
        };

        $exceptions->render(function (ValidationException $exception, Request $request) use ($renderJsonError) {
            return $renderJsonError(
                $request,
                'Erreur de validation',
                $exception->status,
                $exception->errors()
            );
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($renderJsonError) {
            return $renderJsonError(
                $request,
                'Non autorisé',
                401,
                ['auth' => [$exception->getMessage() ?: 'Authentification requise']]
            );
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($renderJsonError) {
            return $renderJsonError(
                $request,
                'Ressource introuvable',
                404
            );
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($renderJsonError) {
            return $renderJsonError(
                $request,
                $exception->getMessage() ?: 'Erreur HTTP',
                $exception->getStatusCode()
            );
        });
    })->create();
