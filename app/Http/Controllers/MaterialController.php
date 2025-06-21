<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-materials');

        $materials = Material::all();

        return Inertia::render('Materials/Index', [
            'materials' => $materials
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-material');

        return Inertia::render('Materials/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-material');

        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'measure_per_jirangan' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            Material::create([
                'name' => $request->name,
                'stock' => $request->stock,
                'measure_per_jirangan' => $request->measure_per_jirangan
            ]);

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku ' . $request->name . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan bahan baku: ' . $th->getMessage())
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
        $this->authorize('edit-material');

        $material = Material::find($id);        

        return Inertia::render('Materials/Edit', [
            'material' => $material
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-material');

        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'measure_per_jirangan' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            $material = Material::find($id);
            $material->name = $request->name;
            $material->stock = $request->stock;
            $material->measure_per_jirangan = $request->measure_per_jirangan;
            $material->save();

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui bahan baku: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-material');

        DB::beginTransaction();

        try {
            $material = Material::find($id);
            $material->delete();

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku ' . $material->name . ' berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat menghapus bahan baku: ' . $th->getMessage())
                ->withInput();
        }
    }
}
