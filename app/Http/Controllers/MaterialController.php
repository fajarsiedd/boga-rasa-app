<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
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
            'stock' => 'nullable|integer|min:0'
        ]);

        Material::create([
            'name' => $request->get('name'),
            'stock' => $request->get('stock')
        ]);

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil ditambahkan.');
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
            'discount' => 'nullable|integer|min:0'
        ]);

        $material = Material::find($id);
        $material->name = $request->get('name');
        $material->stock = $request->get('stock');
        $material->save();

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-material');

        $material = Material::find($id);

        $material->delete();

        return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil dihapus.');
    }
}
