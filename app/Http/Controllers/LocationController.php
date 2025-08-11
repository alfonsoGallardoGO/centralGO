<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations = Location::all();
        return Inertia::render('Catalogo/Ubicaciones', [
            'locations' => $locations,
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
        $location = $request->validate([
            'external_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'estate' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);
        Location::create($location);

        return redirect()->route('/catalogo/ubicaciones');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location)
    {
        $data = $request->validate([
            'external_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'estate' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $location->update($data);

        return redirect()->route('/catalogo/ubicaciones');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location)
    {
        $location->delete();

        return redirect()->route('/catalogo/ubicaciones');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        Location::whereIn('id', $ids)->delete();

        return redirect()->route('/catalogo/ubicaciones')->with('success', 'Ubicaciones eliminadas exitosamente.');
    }
}
