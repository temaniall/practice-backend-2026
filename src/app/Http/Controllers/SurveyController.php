<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function store(Request $request)
    {
        if (!auth('api')->user()->isAdmin()) {
            return response()->json(['message' => 'У вас нет прав администратора для создания опросов'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $survey = auth('api')->user()->surveys()->create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'draft'
        ]);

        return response()->json([
            'message' => 'Опрос создан!',
            'survey' => $survey
        ], 201);
    }

    public function index()
    {
        $user = auth('api')->user();

        return response()->json($user->surveys, 200);
    }

    public function show($id)
    {
        $survey = Survey::with('questions.options')->findOrFail($id);

        return response()->json($survey);
    }

    public function changeStatus(Request $request, $id)
    {
        $survey = Survey::findOrFail($id);

        if ($survey->user_id !== auth('api')->id()) {
            return response()->json(['message' => 'Вы не можете менять статус чужого опроса'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,published,closed'
        ]);

        $currentStatus = $survey->status;
        $newStatus = $validated['status'];

        $allowedTransitions = [
            'draft' => ['published'],
            'published' => ['closed'],
            'closed' => []
        ];

        if ($currentStatus === $newStatus) {
            return response()->json(['message' => 'Статус уже установлен', 'survey' => $survey]);
        }

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'message' => "Нарушение жизненного цикла: переход из $currentStatus в $newStatus запрещен"
            ], 422);
        }

        $survey->update(['status' => $newStatus]);

        return response()->json([
            'message' => 'Статус опроса успешно изменен',
            'survey' => $survey
        ]);
    }

    public function showForPassing($id)
    {
        $survey = Survey::with('questions.options')
            ->where('status', 'published')
            ->findOrFail($id);

        return response()->json($survey);
    }
}