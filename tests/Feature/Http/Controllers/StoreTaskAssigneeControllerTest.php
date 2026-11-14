<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTaskAssigneeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_adding_an_assignee(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $this->post(route('tasks.assignees.store', $task), [
            'user_id' => User::factory()->create()->id,
        ])->assertRedirect(route('login'));
    }

    public function test_task_creator_can_add_an_assignee_and_receives_the_assignee_list(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create(['name' => 'Sari Dewi']);
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $otherTask = Task::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($creator)->postJson(route('tasks.assignees.store', $task), [
            'user_id' => $assignee->id,
            'task_id' => $otherTask->id,
        ]);

        $response->assertOk()
            ->assertExactJson([
                'assignees' => [
                    [
                        'id' => $assignee->id,
                        'name' => 'Sari Dewi',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
        $this->assertDatabaseMissing('task_user', [
            'task_id' => $otherTask->id,
            'user_id' => $assignee->id,
        ]);
    }

    public function test_same_user_can_be_assigned_to_a_different_task(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $firstTask = Task::factory()->create(['created_by' => $creator->id]);
        $secondTask = Task::factory()->create(['created_by' => $creator->id]);
        $firstTask->assignees()->attach($assignee);

        $response = $this->actingAs($creator)->postJson(route('tasks.assignees.store', $secondTask), [
            'user_id' => $assignee->id,
        ]);

        $response->assertOk()
            ->assertExactJson([
                'assignees' => [
                    [
                        'id' => $assignee->id,
                        'name' => $assignee->name,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $firstTask->id,
            'user_id' => $assignee->id,
        ]);
        $this->assertDatabaseHas('task_user', [
            'task_id' => $secondTask->id,
            'user_id' => $assignee->id,
        ]);
    }

    public function test_admin_can_add_an_assignee_to_any_task(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $this->actingAs($admin)
            ->postJson(route('tasks.assignees.store', $task), ['user_id' => $assignee->id])
            ->assertOk()
            ->assertExactJson([
                'assignees' => [
                    [
                        'id' => $assignee->id,
                        'name' => $assignee->name,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }

    public function test_duplicate_assignee_is_rejected_with_422_without_adding_a_second_pivot_row(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $task->assignees()->attach($assignee);

        $response = $this->actingAs($creator)
            ->postJson(route('tasks.assignees.store', $task), ['user_id' => $assignee->id]);

        $response->assertUnprocessable()
            ->assertInvalid([
                'user_id' => 'Pengguna tersebut sudah menjadi penanggung jawab.',
            ]);

        $this->assertDatabaseCount('task_user', 1);
    }

    public function test_nonexistent_user_is_rejected_with_422(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($creator)
            ->postJson(route('tasks.assignees.store', $task), ['user_id' => 999999]);

        $response->assertUnprocessable()
            ->assertInvalid([
                'user_id' => 'Pengguna yang dipilih tidak ditemukan.',
            ]);

        $this->assertDatabaseCount('task_user', 0);
    }

    public function test_user_id_is_required(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($creator)
            ->postJson(route('tasks.assignees.store', $task), []);

        $response->assertUnprocessable()
            ->assertInvalid([
                'user_id' => 'Pilih pengguna yang akan ditambahkan.',
            ]);

        $this->assertDatabaseCount('task_user', 0);
    }

    public function test_non_integer_user_id_is_rejected(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($creator)
            ->postJson(route('tasks.assignees.store', $task), ['user_id' => 'bukan-id']);

        $response->assertUnprocessable()
            ->assertInvalid([
                'user_id' => 'Pengguna yang dipilih tidak valid.',
            ]);

        $this->assertDatabaseCount('task_user', 0);
    }

    public function test_member_who_did_not_create_the_task_cannot_add_an_assignee(): void
    {
        $member = User::factory()->create();
        $creator = User::factory()->create();
        $anotherUser = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);
        $task->assignees()->attach($member);

        $this->actingAs($member)
            ->postJson(route('tasks.assignees.store', $task), ['user_id' => $anotherUser->id])
            ->assertForbidden();

        $this->assertDatabaseCount('task_user', 1);
    }

    public function test_non_json_add_redirects_with_a_success_message(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create(['created_by' => $creator->id]);

        $response = $this->actingAs($creator)
            ->from(route('tasks.index'))
            ->post(route('tasks.assignees.store', $task), ['user_id' => $assignee->id]);

        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'Penanggung jawab berhasil ditambahkan.');

        $this->assertDatabaseHas('task_user', [
            'task_id' => $task->id,
            'user_id' => $assignee->id,
        ]);
    }
}
