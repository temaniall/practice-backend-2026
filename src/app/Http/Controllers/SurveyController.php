<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function store(Request $request)
    {
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
}