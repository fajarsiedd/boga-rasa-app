<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-customer-types');

        $customer_types = CustomerType::all();

        return Inertia::render('CustomerTypes/Index', [
            'customer_types' => $customer_types
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-customer-type');

        return Inertia::render('CustomerTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-customer-type');

        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'nullable|integer|min:0|max_digits:3'
        ]);

        CustomerType::create([
            'name' => $request->name,
            'discount' => $request->discount
        ]);

        return redirect()->route('tipe-konsumen.index')->with('success', 'Tipe konsumen berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {        
        $this->authorize('edit-customer-type');

        $customer_type = CustomerType::find($id);

        return Inertia::render('CustomerTypes/Edit', [
            'customer_type' => $customer_type
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {        
        $this->authorize('edit-customer-type');        

        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'nullable|integer|min:0|max_digits:3'
        ]);

        $customer_type = CustomerType::find($id);
        $customer_type->name = $request->get('name');
        $customer_type->discount = $request->get('discount');               
        $customer_type->save();

        return redirect()->route('tipe-konsumen.index')->with('success', 'Tipe konsumen berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-customer-type');

        $customer_type = CustomerType::find($id);

        $customer_type->delete();

        return redirect()->route('tipe-konsumen.index')->with('success', 'Tipe konsumen berhasil dihapus.');
    }
}
