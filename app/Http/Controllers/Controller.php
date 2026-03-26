<?php

namespace App\Http\Controllers;
/**
 * @OA\Info(
 * version="1.0.0",
 * title="Health Assistant API Documentation",
 * description="Documentation de l'API Assistant Santé avec IA (Laravel 12)",
 * @OA\Contact(
 * email="support@healthapi.com"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 * type="http",
 * description="Utilisez un token Sanctum (Bearer)",
 * name="Authorization",
 * in="header",
 * scheme="bearer",
 * bearerFormat="JWT",
 * securityScheme="bearerAuth",
 * )
 */
abstract class Controller
{
    //
}
