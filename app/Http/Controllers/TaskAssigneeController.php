<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskAssigneeRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskAssigneeController extends Controller
{
    public function store(StoreTaskAssigneeRequest $request, Task $task): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $task->assignees()->attach($validated['user_id']);

        if ($request->expectsJson()) {
            return response()->json([
                'assignees' => $this->assigneesPayload($task),
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Penanggung jawab berhasil ditambahkan.');
    }

    public function destroy(Request $request, Task $task, User $user): JsonResponse|RedirectResponse
    {
        Gate::authorize('manageAssignees', $task);

        $detachedAssignees = $task->assignees()->detach($user->id);

        if ($detachedAssignees === 0) {
            abort(404);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'assignees' => $this->assigneesPayload($task),
            ]);
        }

        return redirect()
            ->route('tasks.index')
            ->with('success', 'Penanggung jawab berhasil dihapus.');
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function assigneesPayload(Task $task): array
    {
        return $task->assignees()
            ->orderBy('users.name')
            ->get(['users.id', 'users.name'])
            ->map(static fn (User $assignee): array => [
                'id' => $assignee->id,
                'name' => $assignee->name,
            ])
            ->all();
    }
}
