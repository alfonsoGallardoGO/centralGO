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

        //Permisos en general
        $rol = $user->getRoleNames();
        $roles = Role::all();
        $permsDesdeFecha = Permission::whereDate('created_at', '>=', '2025-08-12')->get();

        $permIdsFromUserRoles = $user->getPermissionsViaRoles()->pluck('id')->all();
        $permIdsCustomRevocados = UserRevokedPermission::where('user_id', $user->id)
            ->pluck('permission_id')
            ->toArray();
        $idsMarcadosRevoked = array_unique(array_merge($permIdsFromUserRoles, $permIdsCustomRevocados));
        $permisos = $permsDesdeFecha->map(function ($perm) use ($idsMarcadosRevoked) {
            return [
                'id'      => $perm->id,
                'name'    => $perm->name,
                'revoked' => in_array($perm->id, $idsMarcadosRevoked),
            ];
        });

        $permisosAll = Permission::whereDate('created_at', '>=', '2025-08-12')
        ->orderBy('created_at', 'desc')
        ->get();

        //Permisos por vista
        $views = Vista::orderBy('modulo') // primero por módulo
            ->orderBy('name')             // luego por nombre
            ->get();

        $permIdsFromUserRoles = $user->getPermissionsViaRoles()->pluck('id')->all();

        

        // 4) Revocados por vista (de tu tabla), agrupados por view_name => [permission_id,...]
        $revokedViewPermissions = UserRevokedViewPermission::where('user_id', $user->id)
            ->get(['view_name','permission_id'])
            ->groupBy('view_name')
            ->map(fn($group) => $group->pluck('permission_id')->all());
        //dd($revokedViewPermissions);


        // 5) Construimos la respuesta por vista
        $basePermsArray = $permsDesdeFecha->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->all();  
        $views = $views->map(function ($view) use (
            $revokedViewPermissions,
            $basePermsArray,
            $permIdsFromUserRoles
        ) {
            
            $revokedForThisView = $revokedViewPermissions->get($view->url, []) ?? [];
            $revokedForThisView = array_map('intval', $revokedForThisView); // normaliza

            // Si tienes revocados globales, súmalos aquí (opcional):
            // $idsMarcadosRevoked = array_unique(array_merge($revokedForThisView, $permIdsCustomRevocadosGlobal));
            $idsMarcadosRevoked = $revokedForThisView;

            $permissions = array_map(function ($perm) use ($idsMarcadosRevoked) {
                $isRevoked = in_array((int)$perm['id'], $idsMarcadosRevoked, true);
                return [
                    'id'      => $perm['id'],
                    'name'    => $perm['name'],
                    'revoked' => !$isRevoked,
                ];
            }, $basePermsArray);

            //dd($permissions);

            return [
                'id'          => $view->id,
                'name'        => $view->name,   // ej: "clientes.index"
                'route'       => $view->url,    // si quieres exponer la ruta real
                'module'      => $view->modulo, // normalizamos el nombre de la key
                'permissions' => $permissions,
            ];
        });

        // 6) Agrupar por módulo (funciona con arrays)
        $groupedViews = $views->groupBy('module');

        return Inertia::render('Usuarios/Edit', [
            'user' => $user,
            'rol' => $rol,
            'roles' => $roles,
            'permisos' => $permisos,
            'permisosAll' => $permisosAll,
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
