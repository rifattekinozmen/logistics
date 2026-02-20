<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     title="Logistics Platform API",
 *     version="1.0.0",
 *     description="Lojistik Yönetim Platformu API Dokümantasyonu",
 *
 *     @OA\Contact(
 *         email="support@logistics.com"
 *     ),
 *
 *     @OA\License(
 *         name="Private",
 *         url="https://logistics.com/license"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Laravel Sanctum token authentication"
 * )
 *
 * @OA\Tag(
 *     name="Driver",
 *     description="Şoför API endpoint'leri"
 * )
 * @OA\Tag(
 *     name="Warehouse",
 *     description="Depo API endpoint'leri"
 * )
 * @OA\Tag(
 *     name="Orders",
 *     description="Sipariş API endpoint'leri"
 * )
 */
abstract class Controller
{
    //
}
