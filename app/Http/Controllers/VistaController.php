<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use App\Models\Vista;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VistaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vistas = Vista::all();
        $modulos = Modulo::all();
        return Inertia::render('Vistas/Index', [
            'vistas' => $vistas,
            'modulos' => $modulos,
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
            'url' => 'required|string|max:255',
            'modulo' => 'required|string|max:255',
        ]);

        Vista::create($validated);

        return redirect()->route('/seguridad/vistas')->with('success', 'Vista creada con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vista $vista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vista $vista)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vista $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'modulo' => 'required|string|max:255',
        ]);

        $id->update($validated);

        return redirect()->route('/seguridad/vistas')->with('success', 'Vista actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vista $id)
    {
        $id->delete();
        return redirect()->route('/seguridad/vistas')->with('success', 'Vista eliminada con éxito.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        Vista::whereIn('id', $ids)->delete();

        return redirect()->route('/seguridad/vistas')->with('success', 'Vistas eliminadas exitosamente.');
    }
}
