<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRevokedPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsuariosController extends Controller
{
    public function index()
    {
        $users = User::all();
        
        return Inertia::render('Usuarios/Index', [
            'users' => $users,
        ]);
    }

    public function edit(User $user)
    {
        $rol = $user->getRoleNames();
        $roles = Role::all();
        $allPermissions = $user->getAllPermissions();

        $revoked = UserRevokedPermission::where('user_id', $user->id)
            ->pluck('permission_id')
            ->toArray();

        $permisos = $allPermissions->map(function ($perm) use ($revoked) {
            return [
                'id' => $perm->id,
                'name' => $perm->name,
                'revoked' => in_array($perm->id, $revoked)
            ];
        });

        $permisosAll = Permission::whereDate('created_at', '>=', '2025-08-12')
        ->orderBy('created_at', 'desc')
        ->get();
        return Inertia::render('Usuarios/Edit', [
            'user' => $user,
            'rol' => $rol,
            'roles' => $roles,
            'permisos' => $permisos,
            'permisosAll' => $permisosAll,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $user->syncRoles($request->input('rol'));
        return redirect()->route('seguridad.usuarios.edit', $user->id)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function updatePermissions(Request $request, User $user)
    {
        //dd($request->all());
        $validated = $request->validate([
            'permissions' => 'array|required',
            'permissions.*.id' => 'integer|exists:permissions,id',
            'permissions.*.revoked' => 'boolean',
        ]);

        //dd($validated);

        foreach ($validated['permissions'] as $perm) {
            if ($perm['revoked']) {
                // Si está revocado y no existe, lo creamos
                UserRevokedPermission::firstOrCreate([
                    'user_id' => $user->id,
                    'permission_id' => $perm['id']
                ]);
            } else {
                // Si NO está revocado y existe, lo eliminamos
                UserRevokedPermission::where('user_id', $user->id)
                    ->where('permission_id', $perm['id'])
                    ->delete();
            }
        }

        return response()->json([
            'message' => 'Permisos actualizados correctamente'
        ]);
    }
}
