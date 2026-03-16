<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(), 
            'content' => $this->faker->sentence() . '?', 
            'type' => 'text', 
            'order' => 1,
        ];
    }
}