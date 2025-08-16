<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRevokedPermission;
use App\Models\UserRevokedViewPermission;
use Spatie\Permission\Models\Permission;

class EffectivePermissions
{
    /**
     * Devuelve un ARRAY de NOMBRES de permisos efectivos para esa vista.
     */
    public function namesFor(User $user, string $viewKey): array
    {
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

        // NOMBRES de permisos efectivos
        return Permission::whereIn('id', $effectiveIds)->pluck('name')->all();
    }

    /**
     * Devuelve MAPA booleano: ['view'=>true, 'create'=>false, ...]
     * Útil si quieres directamente flags en el front.
     */
    public function mapFor(User $user, string $viewKey, array $universe): array
    {
        $names = $this->namesFor($user, $viewKey);
        $set   = array_flip($names);
        $out   = [];
        foreach ($universe as $name) {
            $out[$name] = array_key_exists($name, $set);
        }
        return $out;
    }
}
