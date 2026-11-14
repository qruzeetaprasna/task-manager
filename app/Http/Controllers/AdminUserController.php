<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): JsonResponse|View
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->select(['id', 'name', 'email', 'role'])
            ->orderBy('name')
            ->paginate(15);

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): JsonResponse|RedirectResponse
    {
        $role = $request->validated('role');
        $updatedUser = DB::transaction(function () use ($request, $role, $user): User {
            $admins = User::query()
                ->where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id']);
            $target = User::query()->findOrFail($user->getKey());

            if (
                $target->is($request->user())
                && $target->isAdmin()
                && $role === 'member'
                && $admins->count() === 1
            ) {
                throw ValidationException::withMessages([
                    'role' => 'Admin terakhir tidak dapat mengubah rolenya menjadi member.',
                ]);
            }

            $target->update(['role' => $role]);

            return $target;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $updatedUser->id,
                'name' => $updatedUser->name,
                'email' => $updatedUser->email,
                'role' => $updatedUser->role,
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Role pengguna berhasil diperbarui.');
    }
}
