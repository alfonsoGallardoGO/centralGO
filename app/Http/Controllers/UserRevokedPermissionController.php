<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRevokedPermission;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserRevokedPermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(UserRevokedPermission $userRevokedPermission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserRevokedPermission $userRevokedPermission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserRevokedPermission $userRevokedPermission)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserRevokedPermission $userRevokedPermission)
    {
        //
    }

    public function revoke(Request $request, User $user)
    {
        $permission = Permission::where('name', $request->permission)->firstOrFail();

        UserRevokedPermission::firstOrCreate([
            'user_id' => $user->id,
            'permission_id' => $permission->id,
        ]);

        return back()->with('success', 'Permiso revocado');
    }

    public function restore(Request $request, User $user)
    {
        $permission = Permission::where('name', $request->permission)->firstOrFail();

        UserRevokedPermission::where('user_id', $user->id)
            ->where('permission_id', $permission->id)
            ->delete();

        return back()->with('success', 'Permiso restaurado');
    }
}
