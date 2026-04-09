<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyRoleRequest;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Permission;
use App\Role;
use Inertia\Inertia;

class RolesController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('role_access'), 403);

        $roles = Role::with('permissions')->get();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles->map(fn ($r) => [
                'id' => $r->id,
                'title' => $r->title,
                'permissions' => $r->permissions->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                ])->values(),
            ])->values(),
            'can' => [
                'create' => \Gate::allows('role_create'),
                'show' => \Gate::allows('role_show'),
                'edit' => \Gate::allows('role_edit'),
                'delete' => \Gate::allows('role_delete'),
            ],
        ]);
    }

    public function create()
    {
        abort_unless(\Gate::allows('role_create'), 403);

        $permissions = Permission::all()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
            ])
            ->values();

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request)
    {
        abort_unless(\Gate::allows('role_create'), 403);

        $role = Role::create($request->all());
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index');
    }

    public function edit(Role $role)
    {
        abort_unless(\Gate::allows('role_edit'), 403);

        $permissions = Permission::all()
            ->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
            ])
            ->values();

        $role->load('permissions');

        return Inertia::render('Admin/Roles/Edit', [
            'permissions' => $permissions,
            'role' => [
                'id' => $role->id,
                'title' => $role->title,
                'permission_ids' => $role->permissions->pluck('id')->values(),
            ],
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        abort_unless(\Gate::allows('role_edit'), 403);

        $role->update($request->all());
        $role->permissions()->sync($request->input('permissions', []));

        return redirect()->route('admin.roles.index');
    }

    public function show(Role $role)
    {
        abort_unless(\Gate::allows('role_show'), 403);

        $role->load('permissions');

        return Inertia::render('Admin/Roles/Show', [
            'role' => [
                'id' => $role->id,
                'title' => $role->title,
                'permissions' => $role->permissions->map(fn ($p) => [
                    'id' => $p->id,
                    'title' => $p->title,
                ])->values(),
            ],
            'can' => [
                'edit' => \Gate::allows('role_edit'),
                'delete' => \Gate::allows('role_delete'),
            ],
        ]);
    }

    public function destroy(Role $role)
    {
        abort_unless(\Gate::allows('role_delete'), 403);

        $role->delete();

        return back();
    }

    public function massDestroy(MassDestroyRoleRequest $request)
    {
        Role::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
