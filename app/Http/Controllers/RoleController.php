<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions' => function ($query) {
            $query->whereDate('created_at', '>=', '2025-08-12')
                ->orderBy('created_at', 'desc');
        }])->get();

        $permisos = Permission::whereDate('created_at', '>=', '2025-08-12')
        ->orderBy('created_at', 'desc')
        ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permissions' => $permisos
        ]);
    }

    public function updateRolePermissions(Request $request, $id)
    {
        //dd($request->all());
        $role = Role::find($id);

        $validated = $request->validate([
            'permissions' => 'array|required',
            'permissions.*' => 'string',
        ]);

        $role->syncPermissions($validated['permissions']);
        $role->name = $request->input('name');
        $role->save();

        return redirect()->route('/seguridad/roles')->with('success', 'Permissions updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array|required',
            'permissions.*' => 'string',
        ]);

        $role = Role::create([
        'name' => $validated['name'],
        'guard_name' => 'web',
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->route('/seguridad/roles')->with('success', 'Role created successfully.');
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        $role->delete();

        return redirect()->route('/seguridad/roles')->with('success', 'Role deleted successfully.');
    }
}