<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/register",
     * summary="Регистрация нового пользователя",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name","email","password","password_confirmation"},
     * @OA\Property(property="name", type="string", example="Vadim"),
     * @OA\Property(property="email", type="string", format="email", example="test@test.com"),
     * @OA\Property(property="password", type="string", format="password", example="123456"),
     * @OA\Property(property="password_confirmation", type="string", format="password", example="123456")
     * )
     * ),
     * @OA\Response(response=201, description="Успешно"),
     * @OA\Response(response=400, description="Ошибка валидации")
     * )
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = auth('api')->login($user);

        return response()->json([
            'message' => 'Юзер успешно создан!',
            'user' => $user,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Post(
     * path="/api/login",
     * summary="Авторизация (получение токена)",
     * tags={"Auth"},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="email", type="string", example="test@test.com"),
     * @OA\Property(property="password", type="string", example="123456")
     * )
     * ),
     * @OA\Response(response=200, description="Токен получен"),
     * @OA\Response(response=401, description="Неверные данные")
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'Неверный логин или пароль'], 401);
        }

        return response()->json([
            'message' => 'С возвращением!',
            'token' => $token
        ]);
    }
}