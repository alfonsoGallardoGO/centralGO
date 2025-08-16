<?php

// app/Http/Middleware/HandleInertiaRequests.php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Inertia\Inertia;
use App\Models\UserRevokedPermission;
use App\Models\UserRevokedViewPermission;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Nombre/URI de la ruta que usas como view_name en BD
            'ctx' => [
                'viewKey' => $this->viewKey($request),
            ],

            // Array de nombres de permisos efectivos para esta vista
            'auth' => [
                'user' => $request->user()?->only('id','name','email'),
                'can'  => function () use ($request) {
                    $user = $request->user();
                    if (!$user) return [];

                    $route   = $request->route();
                    $viewKey = $this->viewKey($request);

                    // Asignados (directos + por roles)
                    $assigned = $user->getAllPermissions()->pluck('id')->map(fn($i)=>(int)$i)->all();

                    // Revocados globales
                    $revokedGlobal = UserRevokedPermission::where('user_id', $user->id)
                        ->pluck('permission_id')->map(fn($i)=>(int)$i)->all();

                    // Revocados por vista
                    $revokedView = UserRevokedViewPermission::where('user_id', $user->id)
                        ->where('view_name', $viewKey)
                        ->pluck('permission_id')->map(fn($i)=>(int)$i)->all();

                    // Efectivos = asignados - (revocados globales ∪ revocados vista)
                    $effectiveIds = array_values(array_diff($assigned, $revokedGlobal, $revokedView));

                    return Permission::whereIn('id', $effectiveIds)
                                        ->whereDate('created_at', '>=', '2025-08-12')
                                        ->pluck('name')
                                        ->all();
                },
                'canViews' => function () use ($request) {
                    $user = $request->user();
                    if (!$user) return [];

                    // Rutas del sidebar que quieres evaluar (usa EXACTAMENTE lo que guardas en view_name)
                    $menuPaths = [
                        '/dashboard',
                        '/catalogo/departamentos',
                        '/catalogo/categorias-gastos',
                        '/catalogo/ubicaciones',
                        '/catalogo/cuentas-contables',
                        '/catalogo/clases',
                        '/catalogo/portales',
                        '/seguridad/usuarios',
                        '/seguridad/roles',
                        '/seguridad/permisos',
                        '/seguridad/vistas',
                    ];

                    // 1) permisos asignados (directos + roles)
                    $assigned = $user->getAllPermissions()->pluck('id')->map(fn($i)=>(int)$i)->all();

                    // 2) revocados globales
                    $revokedGlobal = UserRevokedPermission::where('user_id', $user->id)
                        ->pluck('permission_id')->map(fn($i)=>(int)$i)->all();

                    // 3) para cada ruta, resta revocados por vista y verifica si tiene 'view'
                    $out = [];
                    foreach ($menuPaths as $path) {
                        $revokedView = UserRevokedViewPermission::where('user_id', $user->id)
                            ->where('view_name', $path) // OJO: debe coincidir con tu BD
                            ->pluck('permission_id')->map(fn($i)=>(int)$i)->all();

                        $effectiveIds = array_values(array_diff($assigned, $revokedGlobal, $revokedView));

                        // tiene permiso 'view' efectivo para esa vista?
                        $hasView = Permission::whereIn('id', $effectiveIds)
                            ->where('name', 'view')       // <- cambia si usas otro nombre
                            ->exists();

                        $out[$path] = $hasView;
                    }

                    return $out;
                },
            ],
        ]);
    }

    private function viewKey(Request $request): string
    {
        $path = $request->getPathInfo(); // siempre string
        return $path !== '' ? $path : '/';
    }
}
