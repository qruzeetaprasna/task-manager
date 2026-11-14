<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_guest_is_redirected_to_login_from_root(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_is_redirected_to_tasks_from_root(): void
    {
        $this->actingAs(User::factory()->make());

        $response = $this->get('/');

        $response->assertRedirect('/tasks');
    }
}
