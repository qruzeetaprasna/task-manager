<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreTaskRequest $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        $validated = $request->validated();

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_done' => false,
            'created_by' => $user->id,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $task->id,
                'title' => $task->title,
                'is_done' => $task->is_done,
            ], 201);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Tugas berhasil ditambahkan.');
    }
}
