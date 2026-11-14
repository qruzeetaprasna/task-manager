<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_deleting_a_task(): void
    {
        $task = Task::factory()->create();

        $this->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_delete_any_task_and_its_assignee_links(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
        ]);
        $task->assignees()->attach($assignee);

        $response = $this->actingAs($admin)->deleteJson(route('tasks.destroy', $task));

        $response->assertOk()
            ->assertExactJson([
                'message' => 'Tugas berhasil dihapus.',
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
        $this->assertDatabaseMissing('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }

    public function test_member_can_delete_a_task_they_created(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
        ]);

        $this->actingAs($creator)
            ->deleteJson(route('tasks.destroy', $task))
            ->assertOk();

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_member_cannot_delete_a_task_they_are_only_assigned_to(): void
    {
        $member = User::factory()->create();
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
        ]);
        $task->assignees()->attach($member);

        $this->actingAs($member)
            ->deleteJson(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->assertModelExists($task);
        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_non_json_delete_redirects_with_a_success_message(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($creator)
            ->from(route('tasks.index'))
            ->delete(route('tasks.destroy', $task));

        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'Tugas berhasil dihapus.');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
