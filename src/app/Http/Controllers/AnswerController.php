<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Survey;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        if ($survey->status !== 'published') {
            return response()->json(['message' => 'Этот опрос нельзя пройти сейчас'], 403);
        }

        $alreadyAnswered = Answer::where('user_id', auth('api')->id())
                                 ->where('survey_id', $id)
                                 ->exists();

        if ($alreadyAnswered) {
            return response()->json(['message' => 'Вы уже проходили этот опрос'], 403);
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.option_id' => 'nullable|exists:question_options,id',
            'answers.*.text_answer' => 'nullable|string',
        ]);

        foreach ($request->answers as $item) {
            Answer::create([
                'user_id' => auth('api')->id(),
                'survey_id' => $id,
                'question_id' => $item['question_id'],
                'option_id' => $item['option_id'] ?? null,
                'text_answer' => $item['text_answer'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Ответы успешно сохранены!'], 201);
    }
}