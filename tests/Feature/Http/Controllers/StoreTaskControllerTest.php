<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_creating_a_task(): void
    {
        $this->post(route('tasks.store'), [
            'title' => 'Menyiapkan bahan rapat',
        ])->assertRedirect(route('login'));
    }

    public function test_title_is_required(): void
    {
        $this->assertInvalidTaskInput([], [
            'title' => 'Judul tugas wajib diisi dan tidak boleh hanya spasi.',
        ]);
    }

    public function test_title_cannot_be_empty(): void
    {
        $this->assertInvalidTaskInput(['title' => ''], [
            'title' => 'Judul tugas wajib diisi dan tidak boleh hanya spasi.',
        ]);
    }

    public function test_title_cannot_contain_only_whitespace(): void
    {
        $this->assertInvalidTaskInput(['title' => '   '], [
            'title' => 'Judul tugas wajib diisi dan tidak boleh hanya spasi.',
        ]);
    }

    public function test_title_must_not_exceed_100_characters(): void
    {
        $this->assertInvalidTaskInput(['title' => str_repeat('a', 101)], [
            'title' => 'Judul tugas maksimal 100 karakter.',
        ]);
    }

    public function test_title_must_be_a_string(): void
    {
        $this->assertInvalidTaskInput(['title' => ['bukan teks']], [
            'title' => 'Judul tugas harus berupa teks.',
        ]);
    }

    public function test_description_must_be_a_string_when_provided(): void
    {
        $this->assertInvalidTaskInput([
            'title' => 'Menyiapkan bahan rapat',
            'description' => ['bukan teks'],
        ], [
            'description' => 'Deskripsi tugas harus berupa teks.',
        ]);
    }

    public function test_valid_task_with_100_character_title_is_created_for_the_authenticated_user(): void
    {
        $creator = User::factory()->create();
        $otherUser = User::factory()->create();
        $title = str_repeat('a', 100);

        $response = $this->actingAs($creator)->postJson(route('tasks.store'), [
            'title' => $title,
            'created_by' => $otherUser->id,
            'is_done' => true,
        ]);

        $task = Task::query()->firstOrFail();

        $response->assertCreated()
            ->assertExactJson([
                'id' => $task->id,
                'title' => $title,
                'is_done' => false,
            ]);

        $this->assertModelExists($task);
        $this->assertSame($creator->id, $task->created_by);
        $this->assertNull($task->description);
        $this->assertFalse($task->is_done);
    }

    public function test_non_json_task_creation_redirects_with_a_success_message(): void
    {
        $creator = User::factory()->create();

        $response = $this->actingAs($creator)
            ->from(route('tasks.index'))
            ->post(route('tasks.store'), [
                'title' => 'Menyiapkan bahan rapat',
            ]);

        $response->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'Tugas berhasil ditambahkan.');

        $this->assertDatabaseHas('tasks', [
            'title' => 'Menyiapkan bahan rapat',
            'created_by' => $creator->id,
            'is_done' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, string>  $expectedErrors
     */
    private function assertInvalidTaskInput(array $input, array $expectedErrors): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('tasks.store'), $input);

        $response->assertUnprocessable()
            ->assertInvalid($expectedErrors);

        $this->assertDatabaseCount('tasks', 0);
    }
}
