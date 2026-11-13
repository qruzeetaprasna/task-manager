<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->getFinder()->addLocation(base_path('tests/Fixtures/views'));
    }

    public function test_guest_is_redirected_to_login_from_tasks_index(): void
    {
        $this->get(route('tasks.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_sees_all_tasks_in_latest_order_with_eager_loaded_relations(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $olderTask = Task::factory()->create([
            'created_by' => $creator->id,
            'created_at' => now()->subMinute(),
        ]);
        $newerTask = Task::factory()->create([
            'created_by' => $creator->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.index'));

        $response->assertOk()
            ->assertViewIs('tasks.index')
            ->assertViewHas('status', fn (?string $status): bool => $status === null)
            ->assertViewHas('tasks', function (EloquentCollection $tasks) use ($newerTask, $olderTask): bool {
                return $tasks->modelKeys() === [$newerTask->id, $olderTask->id]
                    && $tasks->every(fn (Task $task): bool => $task->relationLoaded('creator') && $task->relationLoaded('assignees'));
            })
            ->assertViewHas('users', fn (EloquentCollection $users): bool => $users->every(
                fn (User $user): bool => array_keys($user->getAttributes()) === ['id', 'name']
            ));
    }

    public function test_done_status_filter_excludes_pending_tasks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $doneTask = Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => true,
        ]);
        Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.index', ['status' => 'done']));

        $response->assertOk()
            ->assertViewHas('status', fn (?string $status): bool => $status === 'done')
            ->assertViewHas('tasks', fn (EloquentCollection $tasks): bool => $tasks->modelKeys() === [$doneTask->id]);
    }

    public function test_pending_status_filter_excludes_done_tasks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => true,
        ]);
        $pendingTask = Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.index', ['status' => 'pending']));

        $response->assertOk()
            ->assertViewHas('status', fn (?string $status): bool => $status === 'pending')
            ->assertViewHas('tasks', fn (EloquentCollection $tasks): bool => $tasks->modelKeys() === [$pendingTask->id]);
    }

    public function test_unknown_status_filter_shows_all_tasks_and_has_no_active_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $doneTask = Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => true,
        ]);
        $pendingTask = Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => false,
        ]);

        $response = $this->actingAs($admin)->get(route('tasks.index', ['status' => 'unknown']));

        $expectedTaskIds = [$doneTask->id, $pendingTask->id];
        sort($expectedTaskIds);

        $response->assertOk()
            ->assertViewHas('status', fn (?string $status): bool => $status === null)
            ->assertViewHas('tasks', function (EloquentCollection $tasks) use ($expectedTaskIds): bool {
                $actualTaskIds = $tasks->modelKeys();
                sort($actualTaskIds);

                return $actualTaskIds === $expectedTaskIds;
            });
    }

    public function test_member_sees_owned_or_assigned_done_tasks_without_leaking_other_statuses_or_tasks(): void
    {
        $member = User::factory()->create();
        $otherMember = User::factory()->create();
        $createdDoneTask = Task::factory()->create([
            'created_by' => $member->id,
            'is_done' => true,
        ]);
        $createdPendingTask = Task::factory()->create([
            'created_by' => $member->id,
            'is_done' => false,
        ]);
        $assignedDoneTask = Task::factory()->create([
            'created_by' => $otherMember->id,
            'is_done' => true,
        ]);
        $assignedDoneTask->assignees()->attach($member);
        $assignedPendingTask = Task::factory()->create([
            'created_by' => $otherMember->id,
            'is_done' => false,
        ]);
        $assignedPendingTask->assignees()->attach($member);
        Task::factory()->create([
            'created_by' => $otherMember->id,
            'is_done' => true,
        ]);

        $response = $this->actingAs($member)->get(route('tasks.index', ['status' => 'done']));

        $expectedTaskIds = [$createdDoneTask->id, $assignedDoneTask->id];
        sort($expectedTaskIds);

        $response->assertOk()
            ->assertViewHas('tasks', function (EloquentCollection $tasks) use ($expectedTaskIds): bool {
                $actualTaskIds = $tasks->modelKeys();
                sort($actualTaskIds);

                return $actualTaskIds === $expectedTaskIds
                    && $tasks->every(fn (Task $task): bool => $task->relationLoaded('creator') && $task->relationLoaded('assignees'));
            });
    }
}
