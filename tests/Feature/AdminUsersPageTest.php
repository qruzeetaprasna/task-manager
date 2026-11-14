<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_page_displays_the_create_form_and_feedback(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->withSession(['status' => 'User berhasil ditambahkan.'])
            ->get(route('admin.users.index'));

        $response->assertOk()
            ->assertSee('x-on:click="$dispatch(\'open-modal\', \'create-user-modal\')"', false)
            ->assertSeeInOrder([
                'method="POST" action="'.route('admin.users.store').'"',
                'name="_token"',
                'name="name"',
                'name="email"',
                'name="password"',
                'name="role"',
                'value="member"',
                'value="admin"',
            ], false)
            ->assertSee('User berhasil ditambahkan.');
    }

    public function test_create_user_form_displays_validation_errors_after_a_failed_submission(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->followingRedirects()
            ->post(route('admin.users.store'), [
                'name' => '',
                'email' => 'new-user@example.com',
                'password' => 'password123',
                'role' => 'member',
            ]);

        $response->assertOk()
            ->assertSee('The name field is required.')
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('style="display: block;"', false);
    }
}
