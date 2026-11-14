<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCreateModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_creation_form_is_rendered_inside_an_alpine_modal(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tasks.index'));

        $response->assertOk()
            ->assertSee('x-on:click="$dispatch(\'open-modal\', \'create-task-modal\')"', false)
            ->assertSee('x-on:open-modal.window', false)
            ->assertSee('aria-modal="true"', false)
            ->assertSee('class="mb-6 flex items-center gap-4"', false)
            ->assertSee('flex min-w-0 flex-1 items-center gap-2 overflow-x-auto', false)
            ->assertSee('shrink-0 items-center gap-2 whitespace-nowrap', false)
            ->assertSee('method="POST"', false)
            ->assertSeeInOrder([
                'action="'.route('tasks.store').'"',
                'name="_token"',
                'name="title"',
                'name="description"',
            ], false)
            ->assertSee('0 / 100')
            ->assertSee('this.charCount > 100', false)
            ->assertSee('@submit.prevent="submitForm"', false);
    }
}
