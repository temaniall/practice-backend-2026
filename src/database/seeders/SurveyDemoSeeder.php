<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SurveyDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@starv.ru'],
            [
                'name' => 'Vadim Starostin',
                'password' => bcrypt('password123'),
            ]
        );

        $survey = $user->surveys()->create([
            'title' => 'Starv Performance Configurator',
            'description' => 'Персонализация вашего BMW M',
            'status' => 'published'
        ]);

        $survey->questions()->create([
            'content' => 'Какая ваша любимая модель BMW?',
            'type' => 'text',
            'order' => 1
        ]);

        $q2 = $survey->questions()->create([
            'content' => 'Выберите основной цвет:',
            'type' => 'radio',
            'order' => 2
        ]);
        $q2->options()->createMany([
            ['option_text' => 'Isle of Man Green'],
            ['option_text' => 'Brooklyn Grey'],
            ['option_text' => 'Frozen Portimao Blue']
        ]);

        $q3 = $survey->questions()->create([
            'content' => 'Выберите карбоновые элементы:',
            'type' => 'checkbox',
            'order' => 3
        ]);
        $q3->options()->createMany([
            ['option_text' => 'Зеркала M-Performance'],
            ['option_text' => 'Задний диффузор'],
            ['option_text' => 'Боковые пороги']
        ]);
    }
}
