<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        Gate::authorize('viewAny', Task::class);

        $status = $request->query('status');

        if (! is_string($status) || ! in_array($status, ['done', 'pending'], true)) {
            $status = null;
        }

        $tasksQuery = Task::query()
            ->with(['creator', 'assignees']);

        if (! $user->isAdmin()) {
            $tasksQuery->where(function (Builder $query) use ($user): void {
                $query->where('created_by', $user->id)
                    ->orWhereHas('assignees', function (Builder $assigneeQuery) use ($user): void {
                        $assigneeQuery->where('users.id', $user->id);
                    });
            });
        }

        if ($status === 'done') {
            $tasksQuery->where('is_done', true);
        } elseif ($status === 'pending') {
            $tasksQuery->where('is_done', false);
        }

        $tasks = $tasksQuery
            ->latest()
            ->get();

        $users = User::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        return view('tasks.index', [
            'tasks' => $tasks,
            'users' => $users,
            'status' => $status,
        ]);
    }
}
