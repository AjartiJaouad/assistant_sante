<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Health Assistant API Documentation',
    description: 'Documentation de l API Assistant Sante avec IA',
    contact: new OA\Contact(email: 'support@healthapi.com')
)]
#[OA\Server(
    url: '/',
    description: 'API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    description: 'Utilisez un token Sanctum (Bearer)',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
abstract class Controller
{
    //
}
