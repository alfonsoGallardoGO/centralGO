<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRevokedPermission;
use App\Models\UserRevokedViewPermission;
use App\Models\Vista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
        $guard = config('auth.defaults.guard');
        $desde = '2025-08-12';

        $rol   = $user->getRoleNames();
        $roles = Role::all();

        // Todos los permisos (del guard) desde la fecha
        $permsDesdeFecha = Permission::whereDate('created_at', '>=', $desde)
            ->orderBy('name')
            ->get();

        // 1) Permisos asignados (directos + por roles)
        $assignedIds = $user->getAllPermissions()->pluck('id')->map('intval')->all();

        // 2) Revocados globales
        $revokedGlobalIds = UserRevokedPermission::where('user_id', $user->id)
            ->pluck('permission_id')->map('intval')->all();

        // --- PERMISOS GENERALES (no por vista) ---
        // enabled = (asignado) && !revocadoGlobal
        $permisos = $permsDesdeFecha->map(function ($perm) use ($assignedIds, $revokedGlobalIds) {
            $enabled = in_array((int)$perm->id, $assignedIds, true)
                && !in_array((int)$perm->id, $revokedGlobalIds, true);

            return [
                'id'      => $perm->id,
                'name'    => $perm->name,
                'enabled' => $enabled,
                // si tu front aún espera 'revoked' como "habilitado" (invertiste semántica antes)
                'revoked' => $enabled,
            ];
        });

        // Si solo usas esto para catálogo sin estados, está bien;
        // pero NO lo uses para pintar toggles porque no trae estados.
        $permisosAll = Permission::whereDate('created_at', '>=', '2025-08-12')
            ->orderBy('created_at', 'desc')
            ->get();
        

        // --- PERMISOS POR VISTA ---
        $views = Vista::orderBy('modulo')->orderBy('name')->get();

        // Trae TODOS los revocados por vista y agrupa para evitar N+1
        $revokedViewMap = UserRevokedViewPermission::where('user_id', $user->id)
            ->get(['view_name', 'permission_id'])
            ->groupBy('view_name')
            ->map(fn ($group) => $group->pluck('permission_id')->map('intval')->all());

        // Lista base de permisos (id, name) para mapear rápido
        $basePermsArray = $permsDesdeFecha
            ->map(fn ($p) => ['id' => (int)$p->id, 'name' => $p->name])
            ->all();

        $views = $views->map(function ($view) use ($revokedViewMap, $basePermsArray, $assignedIds, $revokedGlobalIds) {

            // OJO: usa exactamente lo que guardas en BD (dijiste que es la URL con slash)
            $revokedForThisView = $revokedViewMap->get($view->url, []) ?? [];

            $permissions = array_map(function ($perm) use ($assignedIds, $revokedGlobalIds, $revokedForThisView) {
                // enabled efectivo para la vista:
                // asignado  Y  no revocado global  Y  no revocado en esta vista
                $enabled = in_array((int)$perm['id'], $assignedIds, true)
                    && !in_array((int)$perm['id'], $revokedGlobalIds, true)
                    && !in_array((int)$perm['id'], $revokedForThisView, true);

                return [
                    'id'      => $perm['id'],
                    'name'    => $perm['name'],
                    'enabled' => $enabled,
                    // si tu UI aún usa 'revoked' como "habilitado":
                    'revoked' => $enabled,
                ];
            }, $basePermsArray);

            return [
                'id'          => $view->id,
                'name'        => $view->name,
                'route'       => $view->url,    // en tu BD es URL
                'module'      => $view->modulo,
                'permissions' => $permissions,
            ];
        });

        $groupedViews = $views->groupBy('module');

        return Inertia::render('Usuarios/Edit', [
            'user'         => $user,
            'rol'          => $rol,
            'roles'        => $roles,
            'permisos'     => $permisos,     // <- usa este para toggles generales
            'permisosAll'  => $permisosAll,  // <- solo catálogo/listado
            'groupedViews' => $groupedViews,
        ]);
    }


    public function update(Request $request, User $user)
    {
        $user->syncRoles($request->input('rol'));
        UserRevokedPermission::where('user_id', $user->id)->delete();
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
        foreach ($validated['permissions'] as $perm) {
            if (!$perm['revoked']) {
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

        return redirect()->route('seguridad.usuarios.edit', $user->id)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function updateViewPermissionsForView(Request $request, User $user)
    {
        //dd($request->all());
        $guard = config('auth.defaults.guard');

        $validated = $request->validate([
            'view_name'            => ['required','string'],
            'permissions'          => ['required','array'],
            'permissions.*.id'     => ['required','integer'],
            'permissions.*.revoked'=> ['required','boolean'],
        ]);

        $viewName = $validated['view_name'];
        $items = collect($validated['permissions'])->map(function ($p) {
            $p['revoked'] = filter_var($p['revoked'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
            return $p;
        });

       // dd($items);

        DB::transaction(function () use ($user, $viewName, $items) {
            
            UserRevokedViewPermission::where('user_id', $user->id)
                ->where('view_name', $viewName)
                ->delete();

            
            $toInsert = $items->where('revoked', false)->map(function ($p) use ($user, $viewName) {
                return [
                    'user_id'       => $user->id,
                    'view_name'     => $viewName,
                    'permission_id' => $p['id'],   
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            })->values()->all();

            if (!empty($toInsert)) {
                UserRevokedViewPermission::insert($toInsert);
            }
        });

        DB::commit();

        return redirect()->route('seguridad.usuarios.edit', $user->id)
            ->with('success', 'Permisos de vista actualizados correctamente.');
    }
}
