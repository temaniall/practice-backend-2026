<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function store(Request $request, $surveyId)
    {
        $request->validate([
            'content' => 'required|string|max:500',
            'type' => 'required|string|in:text,radio,checkbox',
            'order' => 'nullable|integer',
        ]);

        $survey = auth('api')->user()->surveys()->findOrFail($surveyId);

        $question = $survey->questions()->create([
            'content' => $request->content,
            'type' => $request->type,
            'order' => $request->order ?? 0
        ]);

        return response()->json([
            'message' => 'Вопрос добавлен!',
            'question' => $question
        ], 201);
    }
}