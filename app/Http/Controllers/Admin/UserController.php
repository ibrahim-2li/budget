<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = $request->string('search')->trim()->value();

        $users = User::query()
            ->with('role')
            ->withCount(['incomes', 'expenses'])
            ->withSum('incomes as incomes_total', 'amount')
            ->withSum('expenses as expenses_total', 'amount')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
                'role' => $user->role?->name,
                'incomes_count' => $user->incomes_count,
                'expenses_count' => $user->expenses_count,
                'incomes_total' => (float) $user->incomes_total,
                'expenses_total' => (float) $user->expenses_total,
                'created_at' => $user->created_at->toDateString(),
            ]);

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'roles' => Role::orderBy('id')->get(['id', 'name']),
            'filters' => ['search' => $search],
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['role_id' => 'You cannot change your own role.']);
        }

        $user->update($request->validated());

        return back()->with('success', "Updated {$user->name}'s role.");
    }
}
