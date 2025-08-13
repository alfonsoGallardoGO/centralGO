<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index() {
        $permisos = Permission::whereDate('created_at', '>=', '2025-08-12')
        ->orderBy('created_at', 'desc')
        ->get();

        return Inertia::render('Permisos/Index', [
            'permissions' => $permisos
        ]);
    }

    public function store(){
        $validated = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('/seguridad/permisos')->with('success', 'Permission created successfully.');
    }

    public function update($id){
        $validated = request()->validate([
            'name' => 'required|string|max:255',
        ]);

        $permission = Permission::findOrFail($id);
        $permission->update([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);

        return redirect()->route('/seguridad/permisos')->with('success', 'Permission updated successfully.');
    }

    public function destroy($id){
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('/seguridad/permisos')->with('success', 'Permission deleted successfully.');
    }

}
