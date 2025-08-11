<?php

namespace App\Http\Controllers;

use App\Models\AccountingAccount;
use Illuminate\Http\Request;

class AccountingAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $accounts = AccountingAccount::all();
        return inertia('Catalogo/CuentasContables', [
            'accounts' => $accounts,
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
            'external_id' => 'required|unique:netsuite_accounting_accounts',
            'name' => 'required|max:255',
            'type' => 'required|max:255',
            'description' => 'nullable|max:255',
            'currency' => 'required|max:255',
            'account_number' => 'required|max:255',
        ]);

        AccountingAccount::create($validated);

        return redirect()->route('/catalogo/cuentas-contables')->with('success', 'Cuenta creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccountingAccount $accountingAccount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountingAccount $accountingAccount)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountingAccount $accountingAccount)
    {
        $validated = $request->validate([
            'external_id' => 'required|unique:netsuite_accounting_accounts,external_id,' . $accountingAccount->id,
            'name' => 'required|max:255',
            'type' => 'required|max:255',
            'description' => 'nullable|max:255',
            'currency' => 'required|max:255',
            'account_number' => 'required|max:255',
        ]);

        $accountingAccount->update($validated);

        return redirect()->route('/catalogo/cuentas-contables')->with('success', 'Cuenta actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountingAccount $accountingAccount)
    {
        $accountingAccount->delete();

        return redirect()->route('/catalogo/cuentas-contables')->with('success', 'Cuenta eliminada exitosamente.');
    }

    public function destroyMultiple(Request $request)
    {
        $ids = $request->input('ids', []);
        AccountingAccount::whereIn('id', $ids)->delete();

        return redirect()->route('/catalogo/cuentas-contables')->with('success', 'Cuentas eliminadas exitosamente.');
    }

}
