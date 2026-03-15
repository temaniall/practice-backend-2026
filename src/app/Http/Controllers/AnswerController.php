<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Survey;
use App\Models\QuestionOption;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function store(Request $request, $id)
    {
        $survey = Survey::with('questions')->findOrFail($id);

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
        ]);

        foreach ($request->answers as $item) {
            $question = $survey->questions->find($item['question_id']);

            if (!$question) {
                return response()->json(['message' => "Вопрос ID {$item['question_id']} не принадлежит этому опросу"], 422);
            }

            if ($question->type === 'text') {
                if (empty($item['text_answer'])) {
                    return response()->json(['message' => "Вопрос '{$question->content}' требует текстовый ответ"], 422);
                }
                if (!empty($item['option_id'])) {
                    return response()->json(['message' => "Для текстового вопроса нельзя указывать вариант (option_id)"], 422);
                }
            } 

            if (in_array($question->type, ['radio', 'checkbox'])) {
                if (empty($item['option_id'])) {
                    return response()->json(['message' => "В вопросе '{$question->content}' нужно выбрать вариант"], 422);
                }
                
                $optionExists = QuestionOption::where('id', $item['option_id'])
                    ->where('question_id', $question->id)
                    ->exists();
                
                if (!$optionExists) {
                    return response()->json(['message' => "Выбранный вариант не подходит к этому вопросу"], 422);
                }
            }
        }

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

    public function getStats($id)
    {
        $survey = Survey::with(['questions.options', 'questions.answers'])->findOrFail($id);

        $totalParticipants = $survey->answers()->distinct('user_id')->count();

        $statistics = $survey->questions->map(function ($question) use ($totalParticipants) {
            $data = [
                'id' => $question->id,
                'question' => $question->content,
                'type' => $question->type,
            ];

            if ($question->type === 'text') {
                $data['answers'] = $question->answers->pluck('text_answer')->filter()->values();
            } else {
                $data['options'] = $question->options->map(function ($option) use ($question) {
                    $count = $question->answers->where('option_id', $option->id)->count();
                    $totalQuestionAnswers = $question->answers->count();
                    
                    return [
                        'text' => $option->option_text,
                        'count' => $count,
                        'percent' => $totalQuestionAnswers > 0 
                            ? round(($count / $totalQuestionAnswers) * 100, 2) 
                            : 0
                    ];
                });
            }

            return $data;
        });

        return response()->json([
            'survey_title' => $survey->title,
            'total_participants' => $totalParticipants,
            'stats' => $statistics
        ]);
    }

    public function export($id)
    {
        $survey = Survey::with(['questions.options', 'answers'])->findOrFail($id);
        return response()->json($survey);
    }
}