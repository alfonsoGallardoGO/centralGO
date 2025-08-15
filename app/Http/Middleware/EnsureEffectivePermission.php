<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Models\UserRevokedPermission;
use App\Models\UserRevokedViewPermission;

class EnsureEffectivePermission
{
    public function handle(Request $request, Closure $next, ...$args)
    {
        $user = $request->user();
        if (!$user) abort(401);

        // Guard real del usuario (más fiable que config())
        $guard = 'web';

        // ----- Parseo de parámetros -----
        // Soporta: 'view.permission:view,export,all'  ó  'view.permission:view,export','all'
        $flat = collect($args)
            ->flatMap(fn($s) => explode(',', (string)$s))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values();

        // Modo (último arg si es 'all' o 'any')
        $last = strtolower((string)$flat->last());
        $mode = in_array($last, ['all','any'], true) ? $last : 'any';
        if (in_array($last, ['all','any'], true)) { $flat->pop(); }

        // Nombres de permisos (únicos)
        $permissionNames = $flat->unique()->values();

        if ($permissionNames->isEmpty()) {
            // Sin requerimientos explícitos -> solo aplica revocados por vista si quieres
            return $next($request);
        }

        // Mapear a IDs válidos para el guard
        $permissionIds = Permission::query()
            ->where('guard_name', $guard)
            ->whereIn('name', $permissionNames)
            ->pluck('id','name')
            ->map(fn($i)=>(int)$i);

        // Si ninguno es válido, corta
        if ($permissionIds->isEmpty()) {
            abort(403, 'Ningún permiso válido para este guard.');
        }

        // ----- Clave de vista (ajusta a tu BD) -----
        // Si en BD guardas el *name* de la ruta: usa getName()
        // Si guardas el *URI/URL* de la ruta: usa uri() o path()
        $route   = $request->route();
        $viewKey = $route?->getName() ?? $request->path(); // cámbialo a ->getName() si BD guarda nombres

        // ----- Permisos efectivos del usuario -----
        // Asignados (directos + por roles, Spatie ya respeta el guard del modelo)
        $assignedIds = $user->getAllPermissions()->pluck('id')->map(fn($i)=>(int)$i)->all();

        

        // Revocados globales
        $revokedGlobal = UserRevokedPermission::where('user_id', $user->id)
            ->pluck('permission_id')->map(fn($i)=>(int)$i)->all();

        // Revocados por esta vista
        $revokedForView = UserRevokedViewPermission::where('user_id', $user->id)
            ->where('view_name', $viewKey)
            ->pluck('permission_id')->map(fn($i)=>(int)$i)->all();
        //dd($revokedForView);
        // Efectivos = asignados - (revocados globales ∪ revocados vista)
        $effectiveAllowed = array_values(array_diff($assignedIds, $revokedGlobal, $revokedForView));

        // ----- Validación (any/all) solo con permisos válidos -----
        $requiredIds = $permissionIds->values()->all();
        if ($mode === 'all') {
            $missing = array_diff($requiredIds, $effectiveAllowed);
            if (!empty($missing)) abort(403, 'No tienes los permisos necesarios para esta vista.');
        } else { // any
            if (count(array_intersect($requiredIds, $effectiveAllowed)) === 0) {
                abort(403, 'No tienes los permisos necesarios para esta vista.');
            }
        }

        return $next($request);
    }
}
