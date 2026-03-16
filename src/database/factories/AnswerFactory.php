<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Survey;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'survey_id' => Survey::factory(),
            'question_id' => Question::factory(),
            'option_id' => null,
            'text_answer' => null,
        ];
    }
}