<?php

namespace App\Http\Controllers;

use App\Models\Portal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PortalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $portals = Portal::all();
        return Inertia::render('Catalogo/Portales', [
            'portals' => $portals
        ]);
    }

    public function indexDashboard() {
        $portals = Portal::all();
        $users = User::all()->count();
        $user = Auth::user()->getRoleNames();

        return Inertia::render('Dashboard', [
            'portals' => $portals,
            'users' => $users,
            'user' => $user
        ]);
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive',
        ]);

        Portal::create($validated);
        return redirect()->route('/catalogo/portales');
    }

    /**
     * Display the specified resource.
     */
    public function show(Portal $portal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Portal $portal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Portal $portal)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'description' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,maintenance,inactive',
        ]);

        $portal->update($validated);
        return redirect()->route('/catalogo/portales');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Portal $portal)
    {
        $portal->delete();
        return redirect()->route('/catalogo/portales');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        Portal::whereIn('id', $ids)->delete();

        return redirect()->route('/catalogo/portales')->with('success', 'Portales eliminados exitosamente.');
    }
}
