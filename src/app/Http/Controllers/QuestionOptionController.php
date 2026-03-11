<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionOptionController extends Controller
{
    public function store(Request $request, $questionId)
    {
        $request->validate([
            'option_text' => 'required|string|max:255',
        ]);

        $question = Question::whereHas('survey', function($query) {
            $query->where('user_id', auth('api')->id());
        })->findOrFail($questionId);

        if ($question->type === 'text') {
            return response()->json([
                'error' => 'Нельзя добавлять варианты ответов к текстовому вопросу'
            ], 422);
        }

        $option = $question->options()->create([
            'option_text' => $request->option_text
        ]);

        return response()->json($option, 201);
    }
}