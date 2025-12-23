<?php

namespace Tests\Feature;

use App\Models\Candidate;
use App\Models\Recruiter;
use App\Models\StatusCategory;
use App\Models\Step;
use App\Models\StepCategory;
use App\Models\StepStatus;
use App\Models\Timeline;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TimelineAPITest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seedStepCategories();
        $this->seedStatusCategories();
    }

    protected function seedStepCategories(): void
    {
        StepCategory::create(['title' => json_encode(['en' => '1st Interview'])]);
        StepCategory::create(['title' => json_encode(['en' => 'Tech Assessment'])]);
        StepCategory::create(['title' => json_encode(['en' => 'Offer'])]);
        StepCategory::create(['title' => json_encode(['en' => 'Other'])]);
    }

    protected function seedStatusCategories(): void
    {
        StatusCategory::create(['title' => 'Pending']);
        StatusCategory::create(['title' => 'Complete']);
        StatusCategory::create(['title' => 'Reject']);
    }

    public function testCreateTimelineSuccess(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();

        $response = $this->postJson('/api/timelines', [
            'recruiter_id' => $recruiter->id,
            'candidate_name' => 'John',
            'candidate_surname' => 'Doe',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'recruiter_id',
                    'candidate_id',
                    'created_at',
                    'updated_at',
                    'steps',
                ],
            ]);

        $this->assertDatabaseHas('candidates', [
            'recruiter_id' => $recruiter->id,
            'name' => 'John',
            'surname' => 'Doe',
        ]);

        $this->assertDatabaseHas('timelines', [
            'recruiter_id' => $recruiter->id,
        ]);
    }

    public function testCreateTimelineRequiresAuthentication(): void
    {
        $recruiter = Recruiter::factory()->create();

        $response = $this->postJson('/api/timelines', [
            'recruiter_id' => $recruiter->id,
            'candidate_name' => 'John',
            'candidate_surname' => 'Doe',
        ]);

        $response->assertStatus(401);
    }

    public function testCreateTimelineValidationErrors(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/timelines', []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.recruiter_id.0', 'The recruiter id field is required.')
            ->assertJsonPath('errors.candidate_name.0', 'The candidate name field is required.')
            ->assertJsonPath('errors.candidate_surname.0', 'The candidate surname field is required.');
    }

    public function testCreateTimelineRecruiterMustExist(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/timelines', [
            'recruiter_id' => 99999,
            'candidate_name' => 'John',
            'candidate_surname' => 'Doe',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.recruiter_id.0', 'The selected recruiter id is invalid.');
    }

    public function testCreateTimelineCreatesCandidate(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();

        $this->postJson('/api/timelines', [
            'recruiter_id' => $recruiter->id,
            'candidate_name' => 'Jane',
            'candidate_surname' => 'Smith',
        ]);

        $this->assertDatabaseHas('candidates', [
            'recruiter_id' => $recruiter->id,
            'name' => 'Jane',
            'surname' => 'Smith',
        ]);
    }

    public function testCreateTimelineResponseStructure(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();

        $response = $this->postJson('/api/timelines', [
            'recruiter_id' => $recruiter->id,
            'candidate_name' => 'John',
            'candidate_surname' => 'Doe',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'recruiter_id',
                    'candidate_id',
                    'created_at',
                    'updated_at',
                    'steps',
                ],
            ]);

        $data = $response->json('data');
        $this->assertIsInt($data['id']);
        $this->assertEquals($recruiter->id, $data['recruiter_id']);
        $this->assertIsArray($data['steps']);
    }

    public function testCreateStepSuccess(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'recruiter_id',
                    'timeline_id',
                    'step_category_id',
                    'created_at',
                    'updated_at',
                    'current_status',
                ],
            ]);

        $this->assertDatabaseHas('steps', [
            'timeline_id' => $timeline->id,
            'step_category_id' => $stepCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $step = Step::where('timeline_id', $timeline->id)->first();
        $this->assertNotNull($step);
        $this->assertDatabaseHas('step_statuses', [
            'step_id' => $step->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);
    }

    public function testCreateStepRequiresAuthentication(): void
    {
        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(401);
    }

    public function testCreateStepValidationErrors(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $timeline = Timeline::factory()->create();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.candidate_id.0', 'The candidate id field is required.')
            ->assertJsonPath('errors.recruiter_id.0', 'The recruiter id field is required.')
            ->assertJsonPath('errors.step_category_id.0', 'The step category id field is required.')
            ->assertJsonPath('errors.status_category_id.0', 'The status category id field is required.');
    }

    public function testCreateStepTimelineMustBelongToCandidate(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate1 = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $candidate2 = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate1->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate2->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.timeline.0', 'Timeline does not belong to the candidate');
    }

    public function testCreateStepTimelineMustBelongToRecruiter(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter1 = Recruiter::factory()->create();
        $recruiter2 = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter1->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter1->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter2->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.timeline.0', 'Timeline does not belong to the recruiter');
    }

    public function testCreateStepCandidateMustBelongToRecruiter(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter1 = Recruiter::factory()->create();
        $recruiter2 = Recruiter::factory()->create();
        $candidate1 = Candidate::factory()->create(['recruiter_id' => $recruiter1->id]);
        $candidate2 = Candidate::factory()->create(['recruiter_id' => $recruiter2->id]);

        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter1->id,
            'candidate_id' => $candidate1->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate2->id,
            'recruiter_id' => $recruiter1->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.timeline.0', 'Timeline does not belong to the candidate');
    }

    public function testCreateStepCategoryUniqueness(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ])->assertStatus(201);

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.step_category.0', 'A step category needs to exist only once per timeline');
    }

    public function testCreateStepCreatesInitialStatus(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $step = Step::where('timeline_id', $timeline->id)->first();
        $this->assertNotNull($step);
        $this->assertDatabaseHas('step_statuses', [
            'step_id' => $step->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);
    }

    public function testCreateStepResponseStructure(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/timelines/{$timeline->id}/steps", [
            'candidate_id' => $candidate->id,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $stepCategory->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'recruiter_id',
                    'timeline_id',
                    'step_category_id',
                    'created_at',
                    'updated_at',
                    'current_status',
                ],
            ]);
    }

    public function testCreateStatusSuccess(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate->id,
            'timeline_id' => $timeline->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'step_id',
                    'recruiter_id',
                    'status_category_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($step->id, $responseData['step_id']);
        $this->assertEquals($statusCategory->id, $responseData['status_category_id']);
        $this->assertEquals($recruiter->id, $responseData['recruiter_id']);

        $this->assertDatabaseHas('step_statuses', [
            'step_id' => $step->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);
    }

    public function testCreateStatusRequiresAuthentication(): void
    {
        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate->id,
            'timeline_id' => $timeline->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $response->assertStatus(401);
    }

    public function testCreateStatusValidationErrors(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $step = Step::factory()->create();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", []);

        $response->assertStatus(422)
            ->assertJsonPath('errors.candidate_id.0', 'The candidate id field is required.')
            ->assertJsonPath('errors.recruiter_id.0', 'The recruiter id field is required.')
            ->assertJsonPath('errors.timeline_id.0', 'The timeline id field is required.')
            ->assertJsonPath('errors.status_category_id.0', 'The status category id field is required.');
    }

    public function testCreateStatusStepMustBelongToTimeline(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline1 = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $timeline2 = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline1->id,
        ]);
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate->id,
            'timeline_id' => $timeline2->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.timeline_id.0', 'Step does not belong to this timeline');
    }

    public function testCreateStatusTimelineMustBelongToCandidate(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate1 = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $candidate2 = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate1->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate2->id,
            'timeline_id' => $timeline->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.candidate_id.0', 'Timeline does not belong to this candidate');
    }

    public function testCreateStatusStepMustBelongToRecruiter(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter1 = Recruiter::factory()->create();
        $recruiter2 = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter1->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter1->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter1->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate->id,
            'timeline_id' => $timeline->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter2->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('errors.recruiter_id.0', 'Step does not belong to this recruiter');
    }

    public function testCreateStatusAddsToHistory(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory1 = StatusCategory::where('title', 'Pending')->first();
        $statusCategory2 = StatusCategory::where('title', 'Complete')->first();

        StepStatus::factory()->create([
            'step_id' => $step->id,
            'recruiter_id' => $recruiter->id,
            'status_category_id' => $statusCategory1->id,
        ]);

        $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate->id,
            'timeline_id' => $timeline->id,
            'status_category_id' => $statusCategory2->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $this->assertEquals(2, $step->statuses()->count());
        $this->assertDatabaseHas('step_statuses', [
            'step_id' => $step->id,
            'status_category_id' => $statusCategory2->id,
        ]);
    }

    public function testCreateStatusResponseStructure(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory = StatusCategory::first();

        $response = $this->postJson("/api/steps/{$step->id}/statuses", [
            'candidate_id' => $candidate->id,
            'timeline_id' => $timeline->id,
            'status_category_id' => $statusCategory->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'step_id',
                    'recruiter_id',
                    'status_category_id',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function testGetTimelineSuccess(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory = StepCategory::first();
        $statusCategory = StatusCategory::first();

        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
            'step_category_id' => $stepCategory->id,
        ]);

        StepStatus::factory()->create([
            'step_id' => $step->id,
            'recruiter_id' => $recruiter->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response = $this->getJson("/api/timelines/{$timeline->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'recruiter_id',
                    'candidate_id',
                    'created_at',
                    'updated_at',
                    'steps' => [
                        '*' => [
                            'id',
                            'recruiter_id',
                            'timeline_id',
                            'step_category_id',
                            'created_at',
                            'updated_at',
                            'current_status',
                        ],
                    ],
                ],
            ]);

        $data = $response->json('data');
        $this->assertEquals($timeline->id, $data['id']);
        $this->assertCount(1, $data['steps']);
    }

    public function testGetTimelineRequiresAuthentication(): void
    {
        $timeline = Timeline::factory()->create();

        $response = $this->getJson("/api/timelines/{$timeline->id}");

        $response->assertStatus(401);
    }

    public function testGetTimelineReturnsOnlyCurrentStatus(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $step = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
        ]);
        $statusCategory1 = StatusCategory::where('title', 'Pending')->first();
        $statusCategory2 = StatusCategory::where('title', 'Complete')->first();

        StepStatus::factory()->create([
            'step_id' => $step->id,
            'recruiter_id' => $recruiter->id,
            'status_category_id' => $statusCategory1->id,
            'created_at' => now()->subHour(),
        ]);

        StepStatus::factory()->create([
            'step_id' => $step->id,
            'recruiter_id' => $recruiter->id,
            'status_category_id' => $statusCategory2->id,
            'created_at' => now(),
        ]);

        $response = $this->getJson("/api/timelines/{$timeline->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $stepData = $data['steps'][0];
        $currentStatus = $stepData['current_status'];

        $this->assertNotNull($currentStatus);
        $this->assertEquals($statusCategory2->id, $currentStatus['status_category_id']);
    }

    public function testGetTimelineReturnsAllSteps(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();
        $candidate = Candidate::factory()->create(['recruiter_id' => $recruiter->id]);
        $timeline = Timeline::factory()->create([
            'recruiter_id' => $recruiter->id,
            'candidate_id' => $candidate->id,
        ]);
        $stepCategory1 = StepCategory::first();
        $stepCategory2 = StepCategory::skip(1)->first();
        $statusCategory = StatusCategory::first();

        $step1 = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
            'step_category_id' => $stepCategory1->id,
        ]);

        $step2 = Step::factory()->create([
            'recruiter_id' => $recruiter->id,
            'timeline_id' => $timeline->id,
            'step_category_id' => $stepCategory2->id,
        ]);

        StepStatus::factory()->create([
            'step_id' => $step1->id,
            'recruiter_id' => $recruiter->id,
            'status_category_id' => $statusCategory->id,
        ]);

        StepStatus::factory()->create([
            'step_id' => $step2->id,
            'recruiter_id' => $recruiter->id,
            'status_category_id' => $statusCategory->id,
        ]);

        $response = $this->getJson("/api/timelines/{$timeline->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data['steps']);
    }

    public function testGetTimelineResponseStructure(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $timeline = Timeline::factory()->create();

        $response = $this->getJson("/api/timelines/{$timeline->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'recruiter_id',
                    'candidate_id',
                    'created_at',
                    'updated_at',
                    'steps',
                ],
            ]);
    }

    public function testGetTimelineWithNoSteps(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $timeline = Timeline::factory()->create();

        $response = $this->getJson("/api/timelines/{$timeline->id}");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data['steps']);
        $this->assertCount(0, $data['steps']);
    }

    public function testCompleteRecruitmentFlow(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $recruiter = Recruiter::factory()->create();

        $firstInterviewCategory = StepCategory::first();
        $techAssessmentCategory = StepCategory::skip(1)->first();
        $pendingStatus = StatusCategory::where('title', 'Pending')->first();
        $completeStatus = StatusCategory::where('title', 'Complete')->first();

        $createTimelineResponse = $this->postJson('/api/timelines', [
            'recruiter_id' => $recruiter->id,
            'candidate_name' => 'John',
            'candidate_surname' => 'Doe',
        ]);

        $createTimelineResponse->assertStatus(201);
        $timelineData = $createTimelineResponse->json('data');
        $timelineId = $timelineData['id'];
        $candidateId = $timelineData['candidate_id'];

        $this->assertDatabaseHas('candidates', [
            'id' => $candidateId,
            'recruiter_id' => $recruiter->id,
            'name' => 'John',
            'surname' => 'Doe',
        ]);

        $createStepResponse = $this->postJson("/api/timelines/{$timelineId}/steps", [
            'candidate_id' => $candidateId,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $firstInterviewCategory->id,
            'status_category_id' => $pendingStatus->id,
        ]);

        $createStepResponse->assertStatus(201);
        $stepData = $createStepResponse->json('data');
        $stepId = $stepData['id'];

        $this->assertDatabaseHas('steps', [
            'id' => $stepId,
            'timeline_id' => $timelineId,
            'step_category_id' => $firstInterviewCategory->id,
        ]);

        $this->assertDatabaseHas('step_statuses', [
            'step_id' => $stepId,
            'status_category_id' => $pendingStatus->id,
        ]);

        $createStatusResponse = $this->postJson("/api/steps/{$stepId}/statuses", [
            'candidate_id' => $candidateId,
            'timeline_id' => $timelineId,
            'status_category_id' => $completeStatus->id,
            'recruiter_id' => $recruiter->id,
        ]);

        $createStatusResponse->assertStatus(201);

        $this->assertDatabaseHas('step_statuses', [
            'step_id' => $stepId,
            'status_category_id' => $completeStatus->id,
        ]);

        $step = Step::find($stepId);
        $this->assertEquals(2, $step->statuses()->count());

        $currentStatus = $step->currentStatus();
        $this->assertNotNull($currentStatus);
        $this->assertEquals($completeStatus->id, $currentStatus->status_category_id);

        $createSecondStepResponse = $this->postJson("/api/timelines/{$timelineId}/steps", [
            'candidate_id' => $candidateId,
            'recruiter_id' => $recruiter->id,
            'step_category_id' => $techAssessmentCategory->id,
            'status_category_id' => $pendingStatus->id,
        ]);

        $createSecondStepResponse->assertStatus(201);
        $secondStepData = $createSecondStepResponse->json('data');
        $secondStepId = $secondStepData['id'];

        $this->assertDatabaseHas('steps', [
            'id' => $secondStepId,
            'timeline_id' => $timelineId,
            'step_category_id' => $techAssessmentCategory->id,
        ]);

        $getTimelineResponse = $this->getJson("/api/timelines/{$timelineId}");

        $getTimelineResponse->assertStatus(200);
        $timelineResponseData = $getTimelineResponse->json('data');

        $this->assertEquals($timelineId, $timelineResponseData['id']);
        $this->assertEquals($recruiter->id, $timelineResponseData['recruiter_id']);
        $this->assertEquals($candidateId, $timelineResponseData['candidate_id']);
        $this->assertCount(2, $timelineResponseData['steps']);

        $firstStepInResponse = collect($timelineResponseData['steps'])->firstWhere('id', $stepId);
        $this->assertNotNull($firstStepInResponse);
        $this->assertNotNull($firstStepInResponse['current_status']);
        $this->assertEquals($completeStatus->id, $firstStepInResponse['current_status']['status_category_id']);

        $secondStepInResponse = collect($timelineResponseData['steps'])->firstWhere('id', $secondStepId);
        $this->assertNotNull($secondStepInResponse);
        $this->assertNotNull($secondStepInResponse['current_status']);
        $this->assertEquals($pendingStatus->id, $secondStepInResponse['current_status']['status_category_id']);
    }
}
