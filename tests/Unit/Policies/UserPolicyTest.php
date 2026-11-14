<?php

namespace Tests\Unit\Policies;

use App\Models\User;
use App\Policies\UserPolicy;
use PHPUnit\Framework\TestCase;

class UserPolicyTest extends TestCase
{
    public function test_admin_can_view_the_user_list_and_update_a_user_role(): void
    {
        $admin = new User;
        $admin->role = 'admin';
        $target = new User;

        $policy = new UserPolicy;

        $this->assertSame(true, $policy->viewAny($admin));
        $this->assertSame(true, $policy->updateRole($admin, $target));
    }

    public function test_member_cannot_view_the_user_list_or_update_a_user_role(): void
    {
        $member = new User;
        $member->role = 'member';
        $target = new User;

        $policy = new UserPolicy;

        $this->assertSame(false, $policy->viewAny($member));
        $this->assertSame(false, $policy->updateRole($member, $target));
    }
}
