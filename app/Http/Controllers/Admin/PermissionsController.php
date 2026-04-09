<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyPermissionRequest;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Permission;
use Inertia\Inertia;

class PermissionsController extends Controller
{
    public function index()
    {
        abort_unless(\Gate::allows('permission_access'), 403);

        $permissions = Permission::all();

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions->map(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
            ])->values(),
            'can' => [
                'create' => \Gate::allows('permission_create'),
                'show' => \Gate::allows('permission_show'),
                'edit' => \Gate::allows('permission_edit'),
                'delete' => \Gate::allows('permission_delete'),
            ],
        ]);
    }

    public function create()
    {
        abort_unless(\Gate::allows('permission_create'), 403);

        return Inertia::render('Admin/Permissions/Create');
    }

    public function store(StorePermissionRequest $request)
    {
        abort_unless(\Gate::allows('permission_create'), 403);

        $permission = Permission::create($request->all());

        return redirect()->route('admin.permissions.index');
    }

    public function edit(Permission $permission)
    {
        abort_unless(\Gate::allows('permission_edit'), 403);

        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'title' => $permission->title,
            ],
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        abort_unless(\Gate::allows('permission_edit'), 403);

        $permission->update($request->all());

        return redirect()->route('admin.permissions.index');
    }

    public function show(Permission $permission)
    {
        abort_unless(\Gate::allows('permission_show'), 403);

        return Inertia::render('Admin/Permissions/Show', [
            'permission' => [
                'id' => $permission->id,
                'title' => $permission->title,
            ],
            'can' => [
                'edit' => \Gate::allows('permission_edit'),
                'delete' => \Gate::allows('permission_delete'),
            ],
        ]);
    }

    public function destroy(Permission $permission)
    {
        abort_unless(\Gate::allows('permission_delete'), 403);

        $permission->delete();

        return back();
    }

    public function massDestroy(MassDestroyPermissionRequest $request)
    {
        Permission::whereIn('id', request('ids'))->delete();

        return response(null, 204);
    }
}
