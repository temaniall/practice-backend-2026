<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Answer;
use App\Models\Survey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SurveyDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@starv.ru'],
            [
                'name' => 'Vadim Starostin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        $survey = $admin->surveys()->create([
            'title' => 'Starv Performance Configurator',
            'description' => 'Персонализация вашего BMW M',
            'status' => 'published'
        ]);

        $q1 = $survey->questions()->create([
            'content' => 'Какая ваша любимая модель BMW?',
            'type' => 'text',
            'order' => 1
        ]);

        $q2 = $survey->questions()->create([
            'content' => 'Выберите основной цвет:',
            'type' => 'radio',
            'order' => 2
        ]);
        $q2_options = $q2->options()->createMany([
            ['option_text' => 'Isle of Man Green'],
            ['option_text' => 'Brooklyn Grey'],
            ['option_text' => 'Frozen Portimao Blue']
        ]);

        $q3 = $survey->questions()->create([
            'content' => 'Выберите карбоновые элементы:',
            'type' => 'checkbox',
            'order' => 3
        ]);
        $q3_options = $q3->options()->createMany([
            ['option_text' => 'Зеркала M-Performance'],
            ['option_text' => 'Задний диффузор'],
            ['option_text' => 'Боковые пороги']
        ]);

        $fakeUsers = [
            ['name' => 'Antokha', 'email' => 'anton@test.ru'],
            ['name' => 'Bratva_1', 'email' => 'brat1@test.ru'],
        ];

        foreach ($fakeUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                ]
            );

            Answer::create([
                'user_id' => $user->id,
                'survey_id' => $survey->id,
                'question_id' => $q1->id,
                'text_answer' => 'BMW M4 Competition'
            ]);

            Answer::create([
                'user_id' => $user->id,
                'survey_id' => $survey->id,
                'question_id' => $q2->id,
                'option_id' => $q2_options[0]->id 
            ]);

            Answer::create([
                'user_id' => $user->id,
                'survey_id' => $survey->id,
                'question_id' => $q3->id,
                'option_id' => $q3_options[0]->id
            ]);
        }
    }
}