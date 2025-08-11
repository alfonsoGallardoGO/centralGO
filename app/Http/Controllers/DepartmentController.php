<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::all();
        return Inertia::render('Catalogo/Departamentos', [
            'departments' => $departments,
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
        $request->validate([
            'name' => 'required|string|max:255',
            'external_id' => 'required|integer|unique:netsuite_departments,external_id',
        ]);

        Department::create($request->all());

        return redirect()->route('/catalogo/departamentos')->with('success', 'Department created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'external_id' => 'required|integer|unique:netsuite_departments,external_id,' . $department->id,
        ]);

        $department->update($request->all());

        return redirect()->route('/catalogo/departamentos')->with('success', 'Departamento actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()->route('/catalogo/departamentos')->with('success', 'Departamento eliminado exitosamente.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        Department::whereIn('id', $ids)->delete();

        return redirect()->route('/catalogo/departamentos')->with('success', 'Departamentos eliminados exitosamente.');
    }
}
