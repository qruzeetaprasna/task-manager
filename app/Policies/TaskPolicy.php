<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if($user->isAdmin()){
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        $is_creator = $task->created_by === $user->id;

        return $is_creator || $$task->assignees()->where('user_id', $user->id)->exists();
    }


    public function create(User $user): bool
    {
        return true;
    }


    public function update(User $user, Task $task): bool
    {
        $is_creator = $task->created_by === $user->id;

        return $is_creator || $$task->assignees()->where('user_id', $user->id)->exists();
    }


    public function delete(User $user, Task $task): bool
    {
        $is_creator = $task->created_by === $user->id;

        return $is_creator;
    }

    public function manageAssignees(User $user, Task $task): bool
    {

        return $task->created_by === $user->id;
    }

}
