<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $expenses = Expense::all();
        return Inertia::render('Catalogo/CategoriasGasto', [
            'expenses' => $expenses,
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
            'external_id' => 'required|integer|unique:netsuite_expense_categories,external_id',
            'description' => 'nullable|string|max:500',
            'account' => 'required|string|max:255',
        ]);

        Expense::create($request->all());

        return redirect()->route('/catalogo/categorias-gastos')->with('success', 'Expense created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'external_id' => 'required|integer|unique:netsuite_expense_categories,external_id,' . $expense->id,
            'description' => 'nullable|string|max:500',
            'account' => 'required|string|max:255',
        ]);

        $expense->update($request->all());

        return redirect()->route('/catalogo/categorias-gastos')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('/catalogo/categorias-gastos')->with('success', 'Expense deleted successfully.');
    }

    /**
     * Remove multiple resources from storage.
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:netsuite_expense_categories,id',
        ]);

        // Elimina los registros que coincidan con los IDs
        Expense::whereIn('id', $request->ids)->delete();

        return redirect()->back()->with('success', 'Gastos eliminados exitosamente.');
    
    }
}