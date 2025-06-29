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
    public function index(Request $request)
    {
        $this->authorize('view-purchases');

        $purchasesQuery = Purchase::with('details.material', 'supplier')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $purchasesQuery->where(function ($query) use ($searchTerm) {
                $query->where('code', 'like', $searchTerm)
                    ->orWhereHas('supplier', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($request->has('date') && $request->date != null) {
            $purchasesQuery->whereDate('created_at', $request->date);
        }

        $purchases = $purchasesQuery->get();

        return Inertia::render('Purchases/Index', [
            'title' => 'Daftar Transaksi Pembelian',
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
            'title' => 'Buat Transaksi Pembelian',
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
                $lastCodeNumber = (int) substr($lastPurchase->code, 2);
                $nextCodeNumber = $lastCodeNumber + 1;
            }

            $purchaseCode = 'P' . str_pad($nextCodeNumber, 4, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $purchaseDetails = [];

            foreach ($request->details as $detail) {
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
                'supplier_id' => $request->supplier_id,
                'total' => $totalAmount,
                'receipt_image' => $receiptImagePath,
            ]);

            foreach ($purchaseDetails as $detailData) {
                $purchase->details()->create($detailData);
            }

            DB::commit();

            return redirect()->route('pembelian.index')
                ->with('success', 'Pembelian ' . $purchaseCode . ' berhasil dibuat.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('pembelian.index')
                ->with('error', 'Terjadi kesalahan saat membuat pembelian: ' . $th->getMessage())
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
            'title' => 'Edit Transaksi Pembelian - ' . $purchase->code,
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
            'receipt_image' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $purchase = Purchase::with('details.material')->find($id);

            foreach ($purchase->details as $oldDetail) {
                $material = Material::find($oldDetail->material_id);
                if ($material) {
                    $material->stock -= $oldDetail->qty;
                    $material->save();
                }
            }

            $purchase->details()->delete();

            $totalAmount = 0;
            $purchaseDetails = [];            

            foreach ($request->details as $detail) {
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

            $receiptImagePath = $purchase->receipt_image;

            if ($request->hasFile('receipt_image')) {
                if ($receiptImagePath) {
                    Storage::disk('public')->delete($receiptImagePath);
                }
                $receiptImagePath = $request->file('receipt_image')->store('purchase_receipts', 'public');
            } elseif ($request->boolean('remove_receipt_image')) {
                if ($receiptImagePath) {
                    Storage::disk('public')->delete($receiptImagePath);
                }
                $receiptImagePath = null;
            }

            $purchase->update([
                'supplier_id' => $request->supplier_id,
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

            foreach ($purchase->details as $detail) {
                $material = Material::find($detail->material_id);
                if ($material) {
                    $material->stock -= $detail->qty;
                    $material->save();
                }
            }

            if ($purchase->receipt_image) {
                Storage::disk('public')->delete($purchase->receipt_image);
            }

            $purchase->details()->delete();
            $purchase->delete();
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
