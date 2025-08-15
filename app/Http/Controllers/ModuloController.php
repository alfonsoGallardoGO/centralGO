<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloController extends Controller
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modulos,name',
        ]);

        Modulo::create($validated);

        return redirect()->route('/seguridad/vistas')->with('success', 'Modulo creado con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Modulo $modulo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Modulo $modulo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Modulo $modulo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:modulos,name,' . $modulo->id,
        ]);

        $modulo->update($validated);

        return redirect()->route('/seguridad/vistas')->with('success', 'Modulo actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modulo $modulo)
    {
        $modulo->delete();

        return redirect()->route('/seguridad/vistas')->with('success', 'Modulo eliminado con éxito.');
    }

    public function destroyMultiple(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:modulos,id',
        ]);

        Modulo::destroy($validated['ids']);

        return redirect()->route('/seguridad/vistas')->with('success', 'Modulos eliminados con éxito.');
    }
}
