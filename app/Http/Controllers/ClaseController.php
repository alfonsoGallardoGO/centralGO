<?php

namespace App\Http\Controllers;

use App\Models\Clase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clases = Clase::all();
        return Inertia::render('Catalogo/Clases', [
            'clases' => $clases
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
            'external_id' => 'required|unique:netsuite_class|max:255',
            'name' => 'required|max:255',
        ]);

        Clase::create($validated);

        return redirect()->route('/catalogo/clases')->with('success', 'Clase creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Clase $clase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Clase $clase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clase $clase)
    {
        $validated = $request->validate([
            'external_id' => 'required',
            'name' => 'required|max:255',
        ]);

        $clase->update($validated);

        return redirect()->route('/catalogo/clases')->with('success', 'Clase actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clase $clase)
    {
        $clase->delete();

        return redirect()->route('/catalogo/clases')->with('success', 'Clase eliminada exitosamente.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        Clase::whereIn('id', $ids)->delete();

        return redirect()->route('/catalogo/clases')->with('success', 'Clases eliminadas exitosamente.');
    }
}
