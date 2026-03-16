<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/surveys",
     * summary="Создать новый опрос (Только для админов)",
     * tags={"Surveys"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"title"},
     * @OA\Property(property="title", type="string", example="Тюнинг выхлопной системы"),
     * @OA\Property(property="description", type="string", example="Опрос о предпочтениях в звуке выхлопа")
     * )
     * ),
     * @OA\Response(response=201, description="Опрос успешно создан"),
     * @OA\Response(response=403, description="Недостаточно прав")
     * )
     */
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

    /**
     * @OA\Get(
     * path="/api/surveys",
     * summary="Получить список всех опросов",
     * tags={"Surveys"},
     * @OA\Parameter(
     * name="status",
     * in="query",
     * description="Фильтр по статусу (draft, published, closed)",
     * required=false,
     * @OA\Schema(type="string")
     * ),
     * @OA\Parameter(
     * name="my",
     * in="query",
     * description="Показать только мои опросы (1)",
     * required=false,
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(response=200, description="Успешный список опросов")
     * )
     */
    public function index(Request $request)
    {
        $query = Survey::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('my')) {
            $query->where('user_id', auth('api')->id());
        }

        if ($request->sort === 'popular') {
            $query->withCount('answers')->orderBy('answers_count', 'desc');
        } else {
            $query->latest();
        }

        return $query->paginate(10);
    }

    /**
     * @OA\Get(
     * path="/api/surveys/{id}",
     * summary="Детальная информация об опросе",
     * tags={"Surveys"},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Данные опроса с вопросами")
     * )
     */
    public function show($id)
    {
        $survey = Survey::with('questions.options')->findOrFail($id);

        return response()->json($survey);
    }

    /**
     * @OA\Patch(
     * path="/api/surveys/{id}/status",
     * summary="Смена статуса опроса",
     * tags={"Surveys"},
     * security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\RequestBody(
     * @OA\JsonContent(
     * required={"status"},
     * @OA\Property(property="status", type="string", enum={"published", "closed"})
     * )
     * ),
     * @OA\Response(response=200, description="Статус изменен"),
     * @OA\Response(response=422, description="Нарушение жизненного цикла")
     * )
     */
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

    /**
     * @OA\Get(
     * path="/api/surveys/{id}/passing",
     * summary="Получить опрос для прохождения (только опубликованные)",
     * tags={"Surveys"},
     * @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     * @OA\Response(response=200, description="Данные для прохождения")
     * )
     */
    public function showForPassing($id)
    {
        $survey = Survey::with('questions.options')
            ->where('status', 'published')
            ->findOrFail($id);

        return response()->json($survey);
    }
}