<?php

namespace App\Http\Controllers;

use App\Models\CustomerType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-customer-types');

        $customerTypesQuery = CustomerType::orderBy('name');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $customerTypesQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            });
        }

        $customerTypes = $customerTypesQuery->get();

        return Inertia::render('CustomerTypes/Index', [
            'title' => 'Daftar Tipe Konsumen',
            'customerTypes' => $customerTypes
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-customer-type');

        return Inertia::render('CustomerTypes/Create', [
            'title' => 'Tambah Tipe Konsumen Baru'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-customer-type');

        $request->validate([
            'name' => 'required|string|max:255',
            'discount' => 'required|integer|min:0|max_digits:3'
        ]);

        DB::beginTransaction();

        try {
            CustomerType::create([
                'name' => $request->name,
                'discount' => $request->discount
            ]);

            DB::commit();

            return redirect()->route('tipe-konsumen.index')->with('success', 'Tipe konsumen ' . $request->name . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('tipe-konsumen.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan tipe konsumen: ' . $th->getMessage())
                ->withInput();
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
        $this->authorize('edit-customer-type');

        $customerType = CustomerType::find($id);

        return Inertia::render('CustomerTypes/Edit', [
            'title' => 'Edit Tipe Konsumen - ' . $customerType->name,
            'customerType' => $customerType
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
            'discount' => 'required|integer|min:0|max_digits:3'
        ]);

        DB::beginTransaction();

        try {
            $customerType = CustomerType::find($id);
            $customerType->name = $request->name;
            $customerType->discount = $request->discount;
            $customerType->save();

            DB::commit();

            return redirect()->route('tipe-konsumen.index')->with('success', 'Tipe konsumen berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('tipe-konsumen.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui tipe konsumen: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-customer-type');

        DB::beginTransaction();

        try {
            $customerType = CustomerType::find($id);
            $customerType->delete();

            DB::commit();

            return redirect()->route('tipe-konsumen.index')->with('success', 'Tipe konsumen ' . $customerType->name . ' berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('tipe-konsumen.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui tipe konsumen: ' . $th->getMessage())
                ->withInput();
        }
    }
}
