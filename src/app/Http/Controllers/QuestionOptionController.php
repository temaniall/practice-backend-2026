<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
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