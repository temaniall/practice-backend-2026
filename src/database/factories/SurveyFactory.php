<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), 
            'title' => 'Опрос: ' . $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => 'draft',
        ];
    }
}