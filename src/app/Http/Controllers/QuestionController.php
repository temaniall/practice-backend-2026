<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/surveys/{surveyId}/questions",
     * summary="Добавить новый вопрос в опрос",
     * tags={"Questions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="surveyId",
     * in="path",
     * required=true,
     * description="ID опроса",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"content", "type"},
     * @OA\Property(property="content", type="string", example="Какой тип тюнинга вас интересует?"),
     * @OA\Property(property="type", type="string", enum={"text", "radio", "checkbox"}),
     * @OA\Property(property="order", type="integer", example=1)
     * )
     * ),
     * @OA\Response(response=201, description="Вопрос успешно добавлен"),
     * @OA\Response(response=403, description="Опрос уже опубликован или закрыт"),
     * @OA\Response(response=404, description="Опрос не найден")
     * )
     */
    public function store(Request $request, $surveyId)
    {
        $survey = auth('api')->user()->surveys()->findOrFail($surveyId);

        if ($survey->status !== 'draft') {
            return response()->json([
                'message' => 'Нельзя добавлять вопросы в опубликованный или закрытый опрос'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|max:500',
            'type' => 'required|string|in:text,radio,checkbox',
            'order' => 'nullable|integer',
        ]);

        $question = $survey->questions()->create([
            'content' => $request->content,
            'type' => $request->type,
            'order' => $request->order ?? 0
        ]);

        return response()->json(['message' => 'Вопрос добавлен!', 'question' => $question], 201);
    }

    /**
     * @OA\Put(
     * path="/api/questions/{id}",
     * summary="Обновить текст вопроса",
     * tags={"Questions"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(
     * name="id",
     * in="path",
     * required=true,
     * description="ID вопроса",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"content"},
     * @OA\Property(property="content", type="string", example="Обновленный текст вопроса")
     * )
     * ),
     * @OA\Response(response=200, description="Вопрос успешно обновлен"),
     * @OA\Response(response=403, description="Доступ запрещен или опрос не в режиме черновика")
     * )
     */
    public function update(Request $request, $id)
    {
        $question = Question::findOrFail($id);
        $survey = $question->survey;

        if ($survey->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Это не ваш опрос'], 403);
        }

        if ($survey->status !== 'draft') {
            return response()->json([
                'message' => 'Нельзя изменять структуру опубликованного или закрытого опроса'
            ], 403);
        }

        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        $question->update([
            'content' => $request->content
        ]);

        return response()->json(['message' => 'Вопрос обновлен', 'question' => $question]);
    }
}