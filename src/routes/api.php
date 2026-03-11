<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SurveyController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuestionOptionController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('/surveys', [SurveyController::class, 'store']);
    Route::get('/surveys', [SurveyController::class, 'index']);
    Route::post('/surveys/{surveyId}/questions', [QuestionController::class, 'store']);
    Route::get('/surveys/{id}', [SurveyController::class, 'show']);
    Route::post('/questions/{questionId}/options', [QuestionOptionController::class, 'store']);
    Route::patch('/surveys/{survey}/status', [\App\Http\Controllers\SurveyController::class, 'changeStatus']);
    Route::get('/surveys/{id}/take', [App\Http\Controllers\SurveyController::class, 'showForPassing']);
    Route::post('/surveys/{id}/answers', [\App\Http\Controllers\AnswerController::class, 'store']);
    Route::put('/questions/{id}', [QuestionController::class, 'update']);
});