<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Role;
use App\User;

use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UsersController extends Controller
{
    public function index()
    {
        abort_unless(Gate::allows('user_access'), 403);

        $users = User::with('roles')->get();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'email_verified_at' => $u->email_verified_at?->toISOString(),
                'roles' => $u->roles->map(fn ($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                ])->values(),
            ])->values(),
            'can' => [
                'create' => Gate::allows('user_create'),
                'show' => Gate::allows('user_show'),
                'edit' => Gate::allows('user_edit'),
                'delete' => Gate::allows('user_delete'),
            ],
        ]);
    }

    public function create()
    {
        abort_unless(Gate::allows('user_create'), 403);

        $roles = Role::all()->map(fn ($r) => [
            'id' => $r->id,
            'title' => $r->title,
        ])->values();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        abort_unless(Gate::allows('user_create'), 403);

        $user = User::create($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function edit(User $user)
    {
        abort_unless(Gate::allows('user_edit'), 403);

        $roles = Role::all()->map(fn ($r) => [
            'id' => $r->id,
            'title' => $r->title,
        ])->values();
        
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'roles' => $roles,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'role_ids' => $user->roles->pluck('id')->values(),
            ],
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        abort_unless(Gate::allows('user_edit'), 403);

        $user->update($request->all());
        $user->roles()->sync($request->input('roles', []));

        return redirect()->route('admin.users.index');
    }

    public function show(User $user)
    {
        abort_unless(Gate::allows('user_show'), 403);

        $user->load('roles');

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'roles' => $user->roles->map(fn ($r) => [
                    'id' => $r->id,
                    'title' => $r->title,
                ])->values(),
            ],
            'can' => [
                'edit' => Gate::allows('user_edit'),
                'delete' => Gate::allows('user_delete'),
            ],
        ]);
    }

    public function destroy(User $user)
    {
        abort_unless(Gate::allows('user_delete'), 403);

        $user->delete();

        return back();
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        User::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
