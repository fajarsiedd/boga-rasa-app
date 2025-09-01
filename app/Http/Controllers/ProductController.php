<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-products');

        $productsQuery = Product::with('ingredients')->orderBy('code');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $productsQuery->where(function ($query) use ($searchTerm) {
                $query->where('code', 'like', $searchTerm)
                    ->orWhere('name', 'like', $searchTerm);
            });
        }

        $products = $productsQuery->get();

        return Inertia::render('Products/Index', [
            'title' => 'Daftar Produk',
            'products' => $products
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-product');
        $materials = Material::orderBy('name')->get();

        return Inertia::render('Products/Create', [
            'title' => 'Tambah Produk Baru',
            'materials' => $materials
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-product');

        $request->validate([
            'code' => 'required|string|max:255|unique:products',
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'produce_per_jirangan' => 'required|integer',
            'ingredients' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $product = Product::create([
                'code' => $request->code,
                'name' => $request->name,
                'stock' => $request->stock,
                'price' => $request->price,
                'produce_per_jirangan' => $request->produce_per_jirangan,
            ]);

            foreach ($request->ingredients as $materialId) {
                $product->ingredients()->create([
                    'material_id' => $materialId
                ]);
            }

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Produk ' . $request->name . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('produk.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan produk: ' . $th->getMessage())
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
        $this->authorize('edit-product');

        $product = Product::with('ingredients')->find($id);
        $materials = Material::orderBy('name')->get();

        return Inertia::render('Products/Edit', [
            'title' => 'Edit Produk - ' . $product->name,
            'product' => $product,
            'materials' => $materials
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-product');

        $request->validate([
            'code' => 'required|string|max:255|unique:products,code,' . $id,
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
            'stock' => 'required|integer',
            'produce_per_jirangan' => 'required|integer',
            'ingredients' => 'required|array|min:1'
        ]);

        DB::beginTransaction();

        try {
            $product = Product::find($id);
            $product->code = $request->code;
            $product->name = $request->name;
            $product->price = $request->price;
            $product->stock = $request->stock;
            $product->produce_per_jirangan = $request->produce_per_jirangan;

            $product->ingredients()->delete();

            foreach ($request->ingredients as $materialId) {
                $product->ingredients()->create([
                    'material_id' => $materialId
                ]);
            }

            $product->save();

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('produk.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui produk: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-product');

        DB::beginTransaction();

        try {
            $product = Product::find($id);
            $product->ingredients()->delete();
            $product->delete();

            DB::commit();

            return redirect()->route('produk.index')->with('success', 'Produk ' . $product->name . ' berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('produk.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui produk: ' . $th->getMessage())
                ->withInput();
        }
    }
}
