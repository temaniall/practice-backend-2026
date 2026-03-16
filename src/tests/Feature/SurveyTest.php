<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Survey;
use App\Models\Question;
use App\Models\Answer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class SurveyTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $token = JWTAuth::fromUser($this->user);
        $this->headers = ['Authorization' => "Bearer $token", 'Accept' => 'application/json'];
    }

    public function test_user_cannot_answer_survey_twice()
    {
        $survey = Survey::factory()->create(['status' => 'published']);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'type' => 'text']);

        Answer::factory()->create([
            'user_id' => $this->user->id,
            'survey_id' => $survey->id,
            'question_id' => $question->id
        ]);

        $response = $this->postJson("/api/surveys/{$survey->id}/answers", [
            'answers' => [['question_id' => $question->id, 'text_answer' => 'Test']]
        ], $this->headers);

        $response->assertStatus(403); 
    }

    public function test_cannot_edit_published_survey()
    {
        $survey = Survey::factory()->create([
            'user_id' => $this->user->id, 
            'status' => 'published'
        ]);

        $response = $this->patchJson("/api/surveys/{$survey->id}/status", ['status' => 'draft'], $this->headers);
        
        $response->assertStatus(422); 
    }

    public function test_validation_for_text_questions()
    {
        $survey = Survey::factory()->create(['status' => 'published']);
        $question = Question::factory()->create(['survey_id' => $survey->id, 'type' => 'text']);

        $response = $this->postJson("/api/surveys/{$survey->id}/answers", [
            'answers' => [['question_id' => $question->id, 'text_answer' => '']]
        ], $this->headers);

        $response->assertStatus(422);
    }

    public function test_cannot_answer_draft_survey()
    {
        $survey = Survey::factory()->create(['status' => 'draft']);
        $question = Question::factory()->create(['survey_id' => $survey->id]);

        $response = $this->postJson("/api/surveys/{$survey->id}/answers", [
            'answers' => [['question_id' => $question->id, 'text_answer' => 'Test']]
        ], $this->headers);

        $response->assertStatus(403);
    }

    public function test_any_user_can_view_stats()
    {
        $otherOwner = User::factory()->create();
        $survey = Survey::factory()->create([
            'user_id' => $otherOwner->id,
            'status' => 'published'
        ]);

        $response = $this->getJson("/api/surveys/{$survey->id}/stats", $this->headers);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'survey_title',
            'total_participants',
            'stats'
        ]);
    }
}