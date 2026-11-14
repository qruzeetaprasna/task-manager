<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_updating_a_task(): void
    {
        $task = Task::factory()->create();

        $this->put(route('tasks.update', $task), [
            'title' => 'Judul yang diperbarui',
        ])->assertRedirect(route('login'));
    }

    public function test_assignee_can_update_task_fields_and_receives_the_json_contract(): void
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'title' => 'Judul lama',
            'description' => 'Deskripsi lama',
            'is_done' => false,
        ]);
        $task->assignees()->attach($assignee);

        $response = $this->actingAs($assignee)->putJson(route('tasks.update', $task), [
            'title' => 'Judul baru',
            'description' => 'Deskripsi baru',
            'is_done' => true,
            'created_by' => $assignee->id,
        ]);

        $response->assertOk()
            ->assertExactJson([
                'id' => $task->id,
                'title' => 'Judul baru',
                'is_done' => true,
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul baru',
            'description' => 'Deskripsi baru',
            'is_done' => true,
            'created_by' => $creator->id,
        ]);
    }

    public function test_checkbox_can_update_only_the_completion_status(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'title' => 'Judul tetap',
            'is_done' => false,
        ]);

        $response = $this->actingAs($creator)->putJson(route('tasks.update', $task), [
            'is_done' => true,
        ]);

        $response->assertOk()
            ->assertExactJson([
                'id' => $task->id,
                'title' => 'Judul tetap',
                'is_done' => true,
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul tetap',
            'is_done' => true,
        ]);
    }

    public function test_member_cannot_update_an_unrelated_task(): void
    {
        $member = User::factory()->create();
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => false,
        ]);

        $this->actingAs($member)
            ->putJson(route('tasks.update', $task), ['is_done' => true])
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_done' => false,
        ]);
    }

    public function test_admin_can_update_a_task_they_did_not_create(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'title' => 'Judul lama',
            'is_done' => false,
        ]);

        $response = $this->actingAs($admin)->putJson(route('tasks.update', $task), [
            'title' => 'Judul admin',
        ]);

        $response->assertOk()
            ->assertExactJson([
                'id' => $task->id,
                'title' => 'Judul admin',
                'is_done' => false,
            ]);
    }

    public function test_empty_title_is_rejected_without_changing_the_task(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'title' => 'Judul tetap',
        ]);

        $response = $this->actingAs($creator)
            ->putJson(route('tasks.update', $task), ['title' => '   ']);

        $response->assertUnprocessable()
            ->assertInvalid([
                'title' => 'Judul tugas wajib diisi dan tidak boleh hanya spasi.',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul tetap',
        ]);
    }

    public function test_title_must_not_exceed_100_characters(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'title' => 'Judul tetap',
        ]);

        $response = $this->actingAs($creator)
            ->putJson(route('tasks.update', $task), ['title' => str_repeat('a', 101)]);

        $response->assertUnprocessable()
            ->assertInvalid([
                'title' => 'Judul tugas maksimal 100 karakter.',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul tetap',
        ]);
    }

    public function test_completion_status_must_be_boolean(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
            'is_done' => false,
        ]);

        $response = $this->actingAs($creator)
            ->putJson(route('tasks.update', $task), ['is_done' => 'selesai']);

        $response->assertUnprocessable()
            ->assertInvalid(['is_done']);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'is_done' => false,
        ]);
    }

    public function test_non_json_update_redirects_with_a_success_message(): void
    {
        $creator = User::factory()->create();
        $task = Task::factory()->create([
            'created_by' => $creator->id,
        ]);

        $response = $this->actingAs($creator)
            ->from(route('tasks.index'))
            ->put(route('tasks.update', $task), [
                'description' => 'Deskripsi baru',
            ]);

        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'Tugas berhasil diperbarui.');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'description' => 'Deskripsi baru',
        ]);
    }
}
