<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20|unique:suppliers',
            ]);

            Supplier::create([
                'name' => $request->name,
                'phone' => $request->phone
            ]);

            DB::commit();

            return redirect()->route('pemasok.index')->with('success', 'Pemasok ' . $request->name . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('pemasok.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan pemasok: ' . $th->getMessage())
                ->withInput();
        }
    }

    public function quickStore(Request $request)
    {
        $this->authorize('create-supplier');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:suppliers',
        ]);

        DB::beginTransaction();

        try {
            $supplier = Supplier::create([
                'name' => $request->name,
                'phone' => $request->phone
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tambah pemasok berhasil',
                'data' => $supplier
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

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

        DB::beginTransaction();

        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:20|unique:suppliers,phone,' . $id,
            ]);

            $supplier = Supplier::find($id);
            $supplier->name = $request->name;
            $supplier->phone = $request->phone;
            $supplier->save();

            DB::commit();

            return redirect()->route('pemasok.index')->with('success', 'Pemasok berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('pemasok.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan pemasok: ' . $th->getMessage())
                ->withInput();
        }
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
