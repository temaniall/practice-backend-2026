<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Survey;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

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