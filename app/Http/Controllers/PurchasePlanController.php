<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\PurchasePlan;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchasePlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-purchase-plans');

        $purchasePlansQuery = PurchasePlan::with('details.material', 'supplier')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $purchasePlansQuery->where(function ($query) use ($searchTerm) {
                $query->where('code', 'like', $searchTerm)
                    ->orWhereHas('supplier', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($request->has('date') && $request->date != null) {
            $purchasePlansQuery->whereDate('created_at', $request->date);
        }

        $purchasePlans = $purchasePlansQuery->get();

        return Inertia::render('PurchasePlans/Index', [
            'title' => 'Daftar Rencana Pembelian',
            'purchasePlans' => $purchasePlans
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-purchase-plan');

        $suppliers = Supplier::orderBy('name')->get();
        $materials = Material::orderBy('name')->get();

        return Inertia::render('PurchasePlans/Create', [
            'title' => 'Buat Rencana Pembelian',
            'suppliers' => $suppliers,
            'materials' => $materials
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-purchase-plan');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'details' => 'required|array|min:1',
            'details.*.material_id' => 'required|exists:materials,id',
            'details.*.qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $purchasePlanDetails = [];

            foreach ($request->details as $detail) {
                $materialId = $detail['material_id'];
                $qty = $detail['qty'];

                $purchasePlanDetails[] = [
                    'material_id' => $materialId,
                    'qty' => $qty
                ];
            }

            $purchasePlan = PurchasePlan::create([
                'status' => 'pending',
                'description' => $request->description,
                'supplier_id' => $request->supplier_id,
            ]);

            foreach ($purchasePlanDetails as $detailData) {
                $purchasePlan->details()->create($detailData);
            }

            DB::commit();

            return redirect()->route('rencana-pembelian.index')
                ->with('success', 'Rencana pembelian berhasil dibuat.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('rencana-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat membuat rencana pembelian: ' . $th->getMessage())
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
        $this->authorize('edit-purchase-plan');

        $purchasePlan = PurchasePlan::with('details.material', 'supplier')->find($id);
        $materials = Material::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return Inertia::render('PurchasePlans/Edit', [
            'title' => 'Setujui Rencana Pembelian',
            'purchasePlan' => $purchasePlan,
            'materials' => $materials,
            'suppliers' => $suppliers
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-purchase-plan');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'details' => 'required|array|min:1',
            'details.*.material_id' => 'required|exists:materials,id',
            'details.*.qty' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();

        try {
            $purchasePlan = PurchasePlan::with('details.material')->find($id);

            $purchasePlan->details()->delete();

            $purchasePlanDetails = [];

            foreach ($request->details as $detail) {
                $materialId = $detail['material_id'];
                $qty = $detail['qty'];

                $purchasePlanDetails[] = [
                    'material_id' => $materialId,
                    'qty' => $qty
                ];
            }

            $purchasePlan->update([
                'supplier_id' => $request->supplier_id,
                'status' => 'approved'
            ]);

            foreach ($purchasePlanDetails as $detail) {
                $purchasePlan->details()->create($detail);
            }

            DB::commit();

            return redirect()->route('rencana-pembelian.index')
                ->with('success', 'Rencana pembelian telah disetujui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('rencana-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui rencana pembelian: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-purchase-plan');

        DB::beginTransaction();

        try {
            $purchasePlan = PurchasePlan::find($id);

            $purchasePlan->details()->delete();
            $purchasePlan->delete();
            DB::commit();

            return redirect()->route('rencana-pembelian.index')
                ->with('success', 'Rencana pembelian berhasil dihapus.');
        } catch (\Exception $th) {
            DB::rollBack();

            return redirect()->route('rencana-pembelian.index')
                ->with('error', 'Terjadi kesalahan saat menghapus rencana pembelian: ' . $th->getMessage());
        }
    }
}
