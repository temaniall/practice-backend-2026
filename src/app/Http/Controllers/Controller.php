<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * title="API",
 * version="1.0.0",
 * description="API системы опросов для преддипломной практики",
 * @OA\Contact(email="vadim.starostin.07@gmail.com")
 * )
 * @OA\Server(
 * url="http://localhost:8000",
 * description="Локальный сервер"
 * )
 * @OA\SecurityScheme(
 * type="http",
 * securityScheme="bearerAuth",
 * scheme="bearer",
 * bearerFormat="JWT"
 * )
 * * @OA\Schema(
 * schema="Survey",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="title", type="string", example="Тюнинг BMW M5"),
 * @OA\Property(property="status", type="string", enum={"draft", "published", "closed"})
 * )
 */
abstract class Controller {}
{

}