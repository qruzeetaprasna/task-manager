<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTaskAssigneeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_removing_an_assignee(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $this->delete(route('tasks.assignees.destroy', [$task, $assignee]))
            ->assertRedirect(route('login'));
    }

    public function test_task_creator_can_remove_an_assignee_and_receives_the_updated_list(): void
    {
        $creator = User::factory()->create();
        $removedAssignee = User::factory()->create(['name' => 'Andi Pratama']);
        $remainingAssignee = User::factory()->create(['name' => 'Sari Dewi']);
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $task->assignees()->attach([$removedAssignee->id, $remainingAssignee->id]);

        $response = $this->actingAs($creator)
            ->deleteJson(route('tasks.assignees.destroy', [$task, $removedAssignee]));

        $response->assertOk()
            ->assertExactJson([
                'assignees' => [
                    [
                        'id' => $remainingAssignee->id,
                        'name' => 'Sari Dewi',
                    ],
                ],
            ]);

        $this->assertDatabaseMissing('task_user', [
            'task_id' => $task->id,
            'user_id' => $removedAssignee->id,
        ]);
    }

    public function test_admin_can_remove_an_assignee_from_any_task(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $task->assignees()->attach($assignee);

        $this->actingAs($admin)
            ->deleteJson(route('tasks.assignees.destroy', [$task, $assignee]))
            ->assertOk()
            ->assertExactJson(['assignees' => []]);

        $this->assertDatabaseMissing('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }

    public function test_member_who_did_not_create_the_task_cannot_remove_an_assignee(): void
    {
        $member = User::factory()->create();
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $task->assignees()->attach($assignee);

        $this->actingAs($member)
            ->deleteJson(route('tasks.assignees.destroy', [$task, $assignee]))
            ->assertForbidden();

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }

    public function test_removing_a_user_who_is_not_assigned_returns_404(): void
    {
        $creator = User::factory()->create();
        $unassignedUser = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $this->actingAs($creator)
            ->deleteJson(route('tasks.assignees.destroy', [$task, $unassignedUser]))
            ->assertNotFound();

        $this->assertModelExists($task);
        $this->assertDatabaseCount('task_user', 0);
    }

    public function test_non_json_removal_redirects_with_a_success_message(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $task->assignees()->attach($assignee);

        $response = $this->actingAs($creator)
            ->from(route('tasks.index'))
            ->delete(route('tasks.assignees.destroy', [$task, $assignee]));

        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'Penanggung jawab berhasil dihapus.');

        $this->assertDatabaseMissing('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }
}
