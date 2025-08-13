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
        //
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
    public function update(Request $request, Vista $vista)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vista $vista)
    {
        //
    }
}
