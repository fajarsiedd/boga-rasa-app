<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-purchases');

        $purchases = Purchase::with('details.material', 'supplier')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-purchase');

        $suppliers = Supplier::orderBy('name')->get();
        $materials = Material::orderBy('name')->get();

        return Inertia::render('Purchases/Create', [
            'suppliers' => $suppliers,
            'materials' => $materials
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-purchase');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'details' => 'required|array|min:1',
            'details.*.material_id' => 'required|exists:materials,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|integer|min:1',
            'receipt_image' => 'nullable|image|max:2048',
        ]);        

        DB::beginTransaction();

        try {
            $lastPurchase = Purchase::orderBy('id', 'desc')->first();
            $nextCodeNumber = 1;

            if ($lastPurchase && $lastPurchase->code) {
                $lastCodeNumber = (int) substr($lastPurchase->code, 1);
                $nextCodeNumber = $lastCodeNumber + 1;
            }

            $purchaseCode = 'B' . str_pad($nextCodeNumber, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $purchaseDetails = [];

            foreach ($request->get('details') as $detail) {
                $materialId = $detail['material_id'];
                $qty = $detail['qty'];
                $subtotal = $detail['subtotal'];

                $purchaseDetails[] = [
                    'material_id' => $materialId,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
                $totalAmount += $subtotal;

                $material = Material::find($materialId);
                if ($material) {
                    $material->stock += $qty;
                    $material->save();
                }
            }            

            $receiptImagePath = null;
            if ($request->hasFile('receipt_image')) {
                $receiptImagePath = $request->file('receipt_image')->store('purchase_receipts', 'public');
            }

            $purchase = Purchase::create([
                'code' => $purchaseCode,
                'supplier_id' => $request->get('supplier_id'),
                'total' => $totalAmount,
                'receipt_image' => $receiptImagePath,
            ]);            

            foreach ($purchaseDetails as $detailData) {
                $purchase->details()->create($detailData);
            }

            DB::commit();

            return redirect()->route('pembelian.index')
                ->with('success', 'Pembelian ' . $purchaseCode . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            // dd($th);
            DB::rollBack();
            return redirect()->route('pembelian.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan pembelian: ' . $th->getMessage())
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
        $this->authorize('edit-purchase');

        $purchase = Purchase::with('details.material', 'supplier')->find($id);
        $materials = Material::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return Inertia::render('Purchases/Edit', [
            'purchase' => $purchase,
            'materials' => $materials,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-purchase');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'details' => 'required|array|min:1',
            'details.*.material_id' => 'required|exists:materials,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.subtotal' => 'required|integer|min:1',
            'receipt_image' => 'nullable|image|max:2048', // Max 2MB
        ]);

        DB::beginTransaction();

        try {
            $purchase = Purchase::with('details.material')->find($id);

            foreach ($purchase->details as $oldDetail) {
                $material = Material::find($oldDetail->material_id);
                if ($material) {
                    $material->stock -= $oldDetail->qty; // Kurangi stok
                    $material->save();
                }
            }

            $purchase->details()->delete();

            $totalAmount = 0;
            $purchaseDetails = [];

            foreach ($request->get('details') as $detail) {
                $materialId = $detail['material_id'];
                $qty = $detail['qty'];
                $subtotal = $detail['subtotal'];                

                $purchaseDetails[] = [
                    'material_id' => $materialId,
                    'qty' => $qty,
                    'subtotal' => $subtotal,
                ];
                $totalAmount += $subtotal;

                // Update stok bahan baku dengan kuantitas baru
                $material = Material::find($materialId);
                if ($material) {
                    $material->stock += $qty; // Tambah stok
                    $material->save();
                }
            }

            $receiptImagePath = $purchase->receipt_image;

            if ($request->hasFile('receipt_image')) {
                // Hapus gambar lama jika ada
                if ($receiptImagePath) {
                    Storage::disk('public')->delete($receiptImagePath);
                }
                $receiptImagePath = $request->file('receipt_image')->store('purchase_receipts', 'public');
            } elseif ($request->boolean('remove_receipt_image')) {
                // Hapus gambar jika ada flag remove_receipt_image dari frontend
                if ($receiptImagePath) {
                    Storage::disk('public')->delete($receiptImagePath);
                }
                $receiptImagePath = null;
            }            

            $purchase->update([
                'supplier_id' => $request->get('supplier_id'),
                'total' => $totalAmount,
                'receipt_image' => $receiptImagePath,
            ]);

            foreach ($purchaseDetails as $detail) {
                $purchase->details()->create($detail);
            }

            DB::commit();

            return redirect()->route('pembelian.index')
                ->with('success', 'Pembelian ' . $purchase->code . ' berhasil diperbarui.');
        } catch (\Throwable $th) {            
            DB::rollBack();            
            return redirect()->route('pembelian.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui pembelian: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-purchase');

        DB::beginTransaction();

        try {
            $purchase = Purchase::find($id);
            // Kembalikan stok bahan baku
            foreach ($purchase->details as $detail) {
                $material = Material::find($detail->material_id);
                if ($material) {
                    $material->stock -= $detail->qty; // Kurangi stok sesuai jumlah yang dihapus
                    $material->save();
                }
            }

            // Hapus gambar nota jika ada
            if ($purchase->receipt_image) {
                Storage::disk('public')->delete($purchase->receipt_image);
            }

            $purchase->details()->delete(); // Hapus detail terkait
            $purchase->delete(); // Hapus pembelian utama
            DB::commit();

            return redirect()->route('pembelian.index')
                ->with('success', 'Pembelian ' . $purchase->code . ' berhasil dihapus.');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('pembelian.index')
                ->with('error', 'Terjadi kesalahan saat menghapus pembelian: ' . $th->getMessage());
        }
    }
}
