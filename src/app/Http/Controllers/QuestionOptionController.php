<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/questions/{questionId}/options",
     * summary="Добавить вариант ответа к вопросу",
     * tags={"Questions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="questionId",
     * in="path",
     * required=true,
     * description="ID вопроса (не текстового)",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"option_text"},
     * @OA\Property(property="option_text", type="string", example="Stage 1 (300 л.с.)")
     * )
     * ),
     * @OA\Response(response=201, description="Вариант успешно добавлен"),
     * @OA\Response(response=403, description="Доступ запрещен или опрос опубликован"),
     * @OA\Response(response=422, description="Нельзя добавить вариант к текстовому вопросу")
     * )
     */
    public function store(Request $request, $questionId)
    {
        $question = Question::whereHas('survey', function($query) {
            $query->where('user_id', auth('api')->id());
        })->findOrFail($questionId);

        if ($question->survey->status !== 'draft') {
            return response()->json([
                'message' => 'Нельзя менять варианты ответов в рабочем опросе'
            ], 403);
        }

        if ($question->type === 'text') {
            return response()->json([
                'error' => 'Нельзя добавлять варианты ответов к текстовому вопросу'
            ], 422);
        }

        $request->validate([
            'option_text' => 'required|string|max:255',
        ]);

        $option = $question->options()->create([
            'option_text' => $request->option_text
        ]);

        return response()->json($option, 201);
    }
}