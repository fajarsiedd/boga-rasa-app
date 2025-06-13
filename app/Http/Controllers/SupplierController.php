<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-suppliers');

        $suppliers = Supplier::all();

        return Inertia::render('Suppliers/Index', [
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-supplier');

        return Inertia::render('Suppliers/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-supplier');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:suppliers',
        ]);

        Supplier::create([
            'name' => $request->name,
            'phone' => $request->phone
        ]);

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function quickStore(Request $request)
    {
        $this->authorize('create-supplier');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:suppliers',
        ]);

        try {
            $supplier = Supplier::create([
                'name' => $request->name,
                'phone' => $request->phone
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tambah pemasok berhasil',
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah pemasok',
                'data' => $th
            ], 500);
        }
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
        $this->authorize('edit-supplier');

        $supplier = Supplier::find($id);

        return Inertia::render('Suppliers/Edit', [
            'supplier' => $supplier
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-supplier');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:suppliers,phone,' . $id,
        ]);

        $supplier = Supplier::find($id);
        $supplier->name = $request->get('name');
        $supplier->phone = $request->get('phone');
        $supplier->save();

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-supplier');

        $supplier = Supplier::find($id);
        $supplier->delete();

        return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
