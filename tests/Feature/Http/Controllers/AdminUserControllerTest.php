<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app['view']->getFinder()->addLocation(base_path('tests/Fixtures/views'));
    }

    public function test_guest_is_redirected_to_login_from_the_admin_user_list(): void
    {
        $this->get(route('admin.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_when_updating_a_user_role(): void
    {
        $user = User::factory()->create();

        $this->patch(route('admin.users.update-role', $user), ['role' => 'admin'])
            ->assertRedirect(route('login'));
    }

    public function test_admin_sees_a_paginated_user_list_without_sensitive_columns(): void
    {
        User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'role' => 'admin',
        ]);
        User::factory()->create([
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'role' => 'member',
        ]);

        $response = $this->actingAs(User::query()->where('email', 'budi@example.com')->firstOrFail())
            ->get(route('admin.users.index'));

        $response->assertOk()
            ->assertViewIs('admin.users.index')
            ->assertViewHas('users', function (LengthAwarePaginator $users): bool {
                return $users->total() === 2
                    && $users->getCollection()->every(
                        fn (User $user): bool => array_keys($user->getAttributes()) === ['id', 'name', 'email', 'role']
                    );
            });
    }

    public function test_admin_can_request_the_user_list_as_json_without_passwords(): void
    {
        $admin = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'admin',
        ]);
        $member = User::factory()->create([
            'name' => 'Sari Dewi',
            'role' => 'member',
        ]);

        $response = $this->actingAs($admin)->getJson(route('admin.users.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPaths([
                'data.0.id' => $admin->id,
                'data.0.name' => 'Budi Santoso',
                'data.0.role' => 'admin',
                'data.1.id' => $member->id,
                'data.1.name' => 'Sari Dewi',
                'data.1.role' => 'member',
            ])
            ->assertJsonMissingPaths([
                'data.0.password',
                'data.0.remember_token',
            ]);
    }

    public function test_member_receives_403_when_viewing_the_admin_user_list(): void
    {
        $member = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->getJson(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_admin_can_change_a_user_role_and_receives_safe_json(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create([
            'name' => 'Sari Dewi',
            'email' => 'sari@example.com',
            'role' => 'member',
        ]);
        $storedPassword = $target->password;

        $response = $this->actingAs($admin)->patchJson(
            route('admin.users.update-role', $target),
            ['role' => 'admin', 'password' => 'should-not-be-returned']
        );

        $response->assertOk()
            ->assertExactJson([
                'id' => $target->id,
                'name' => 'Sari Dewi',
                'email' => 'sari@example.com',
                'role' => 'admin',
            ])
            ->assertJsonMissingPath('password');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'admin',
        ]);
        $this->assertSame($storedPassword, $target->fresh()->password);
    }

    public function test_missing_role_is_rejected_with_422(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($admin)
            ->patchJson(route('admin.users.update-role', $target), []);

        $response->assertUnprocessable()
            ->assertInvalid([
                'role' => 'Role pengguna wajib dipilih.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'member',
        ]);
    }

    public function test_member_receives_403_when_changing_a_user_role(): void
    {
        $member = User::factory()->create(['role' => 'member']);
        $target = User::factory()->create(['role' => 'member']);

        $this->actingAs($member)
            ->patchJson(route('admin.users.update-role', $target), ['role' => 'admin'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'member',
        ]);
    }

    public function test_role_outside_the_allowed_values_is_rejected_with_422(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $target = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($admin)
            ->patchJson(route('admin.users.update-role', $target), ['role' => 'owner']);

        $response->assertUnprocessable()
            ->assertInvalid([
                'role' => 'Role hanya boleh admin atau member.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'member',
        ]);
    }

    public function test_last_admin_receives_422_when_demoting_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->patchJson(route('admin.users.update-role', $admin), ['role' => 'member']);

        $response->assertUnprocessable()
            ->assertInvalid([
                'role' => 'Admin terakhir tidak dapat mengubah rolenya menjadi member.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_last_admin_gets_a_validation_error_when_demoting_themselves_from_a_form(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update-role', $admin), ['role' => 'member']);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors([
                'role' => 'Admin terakhir tidak dapat mengubah rolenya menjadi member.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_demote_themselves_when_another_admin_remains(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->patchJson(route('admin.users.update-role', $admin), ['role' => 'member']);

        $response->assertOk()
            ->assertJsonPath('role', 'member');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'member',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $otherAdmin->id,
            'role' => 'admin',
        ]);
    }

    public function test_non_json_role_update_redirects_with_a_success_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $member = User::factory()->create(['role' => 'member']);

        $response = $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->patch(route('admin.users.update-role', $member), ['role' => 'admin']);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('success', 'Role pengguna berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'role' => 'admin',
        ]);
    }
}
