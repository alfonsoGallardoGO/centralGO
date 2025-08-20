<?php

// app/Http/Middleware/HandleInertiaRequests.php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Illuminate\Support\Facades\Cache;
use App\Models\UserRevokedPermission;
use App\Models\UserRevokedViewPermission;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    /**
     * La vista raíz de Inertia.
     */
    protected $rootView = 'app';

    /**
     * Fecha desde la cual considerar permisos.
     * Ajusta el formato YYYY-MM-DD según necesites.
     */
    private const PERMS_FROM_DATE = '2025-08-12';

    /**
     * Rutas del sidebar a evaluar (deben coincidir EXACTAMENTE con lo guardado en view_name).
     * Si prefieres, mueve esto a config/menu.php y léelo desde allí.
     */
    private const MENU_PATHS = [
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
        '/catalogo/proveedores',
        '/catalogo/usuarios-proveedores',
    ];

    /**
     * Comparte props globales con todas las respuestas de Inertia.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Contexto básico
            'ctx' => [
                // viewKey coincide con lo que guardas en BD (path con slash inicial)
                'viewKey' => $this->viewKey($request),
            ],

            // Datos de autenticación y permisos efectivos
            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email'),

                // Permisos efectivos (nombres) para la vista actual
                'can' => function () use ($request) {
                    $user = $request->user();
                    if (!$user) return [];

                    $viewKey = $this->viewKey($request);
                    $desde   = self::PERMS_FROM_DATE;

                    // 1) baseline permitido por usuario: (asignados - revocados globales), cache 10 min
                    $baseline = Cache::remember("perm:baseline:u:{$user->id}", 600, function () use ($user) {
                        $assigned = $user->getAllPermissions()->pluck('id')->map('intval')->all();
                        $revokedGlobal = UserRevokedPermission::where('user_id', $user->id)
                            ->pluck('permission_id')->map('intval')->all();
                        return array_values(array_diff($assigned, $revokedGlobal)); // ids permitidos base
                    });

                    // 2) revocados por ESTA vista
                    $revokedView = UserRevokedViewPermission::where('user_id', $user->id)
                        ->where('view_name', $viewKey)
                        ->pluck('permission_id')->map('intval')->all();

                    // 3) ids efectivos para esta vista
                    $effectiveIds = array_values(array_diff($baseline, $revokedView));

                    // 4) nombres filtrados por fecha
                    return Permission::whereIn('id', $effectiveIds)
                        ->whereDate('created_at', '>=', $desde)
                        ->pluck('name')
                        ->all();
                },

                // Mapa para sidebar: { '/ruta': true|false } (¿tiene 'view' efectivo en esa ruta?)
                'canViews' => function () use ($request) {
                    $user = $request->user();
                    if (!$user) return [];

                    // Cachea por usuario (10 min). Invalida al editar roles/permisos/revokes.
                    return Cache::remember("perm:canviews:u:{$user->id}", 600, function () use ($user) {
                        // baseline = asignados - revocados globales (una sola vez)
                        $assigned = $user->getAllPermissions()->pluck('id')->map('intval')->all();
                        $revokedGlobal = UserRevokedPermission::where('user_id', $user->id)
                            ->pluck('permission_id')->map('intval')->all();
                        $baseline = array_values(array_diff($assigned, $revokedGlobal));
                        $baselineSet = array_flip($baseline); // para lookup O(1)

                        // todos los revocados por vista del usuario
                        $revokedViewMap = UserRevokedViewPermission::where('user_id', $user->id)
                            ->get(['view_name', 'permission_id'])
                            ->groupBy('view_name')
                            ->map(fn ($g) => $g->pluck('permission_id')->map('intval')->all());

                        // id del permiso 'view' (una sola vez)
                        $viewPermId = (int) (Permission::where('name', 'view')->value('id') ?? 0);

                        $out = [];
                        foreach (self::MENU_PATHS as $path) {
                            $revokeIds = $revokedViewMap->get($path, []);
                            // permitido si: está en baseline y NO está revocado para esa vista
                            $ok = $viewPermId
                                ? (isset($baselineSet[$viewPermId]) && !in_array($viewPermId, $revokeIds, true))
                                : false;
                            $out[$path] = $ok;
                        }

                        return $out;
                    });
                },
            ],
        ]);
    }

    /**
     * Devuelve la clave de vista tal cual tu BD la guarda.
     * Aquí usamos path con slash inicial (p.ej. "/dashboard").
     */
    private function viewKey(Request $request): string
    {
        $path = $request->getPathInfo(); // siempre string, con slash inicial
        return $path !== '' ? $path : '/';
    }
}
