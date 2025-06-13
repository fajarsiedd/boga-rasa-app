<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('view-sales');

        $sales = Sale::with('details.product', 'customer')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Sales/Index', [
            'sales' => $sales
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-sale');

        $customers = Customer::with('customer_type')->orderBy('name')->get();
        $customer_types = CustomerType::all();
        $products = Product::orderBy('name')->get();

        return Inertia::render('Sales/Create', [
            'customers' => $customers,
            'customer_types' => $customer_types,
            'products' => $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-sale');

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'is_paid' => 'boolean',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.final_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $last_sale = Sale::orderBy('id', 'desc')->first();
            $next_code_num = 1;

            if ($last_sale && $last_sale->code) {
                $last_code_num = (int) substr($last_sale->code, 1);
                $next_code_num = $last_code_num + 1;
            }

            $code = 'P' . str_pad($next_code_num, 4, '0', STR_PAD_LEFT);
            $total = 0;
            $sale_details_data = [];

            foreach ($request->get('details') as $detail) {
                $subtotal = $detail['qty'] * $detail['final_price'];

                $sale_details_data[] = [
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'final_price' => $detail['final_price'],
                    'subtotal' => $subtotal,
                ];

                $total += $subtotal;
            }

            $paid_at_value = $request->boolean('is_paid') ? now() : null;

            $sale = Sale::create([
                'code' => $code,
                'customer_id' => $request->get('customer_id'),
                'total' => $total,
                'paid_at' => $paid_at_value
            ]);

            foreach ($sale_details_data as $detail) {
                $sale->details()->create($detail);
            }

            DB::commit();

            return redirect()->route('penjualan.index')->with('success', 'Penjualan ' . $code . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('penjualan.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan penjualan: ' . $th->getMessage())
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
        $this->authorize('edit-sale');

        $sale = Sale::with('details.product', 'customer')->find($id);
        $customers = Customer::with('customer_type')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return Inertia::render('Sales/Edit', [
            'sale' => $sale,
            'customers' => $customers,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-sale');

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'is_paid' => 'boolean',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty' => 'required|integer|min:1',
            'details.*.final_price' => 'required|numeric|min:0', // final_price diterima dari frontend            
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::find($id);
            $total = 0;
            $sale_details_data = [];

            foreach ($request->get('details') as $detail) {
                $subtotal = $detail['qty'] * $detail['final_price'];

                $sale_details_data[] = [
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'final_price' => $detail['final_price'],
                    'subtotal' => $subtotal,
                ];

                $total += $subtotal;
            }

            $paid_at_value = $request->boolean('is_paid') ? now() : null;

            $sale->update([
                'customer_id' => $request->get('customer_id'),
                'paid_at' => $paid_at_value,
                'total' => $total,
            ]);

            $sale->details()->delete();

            foreach ($sale_details_data as $detail) {
                $sale->details()->create($detail);
            }

            DB::commit();

            return redirect()->route('penjualan.index')->with('success', 'Penjualan ' . $sale->code . ' berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('penjualan.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui penjualan: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-sale');

        DB::beginTransaction();

        try {
            $sale = Sale::find($id);
            $sale->details()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('penjualan.index')
                ->with('success', 'Penjualan ' . $sale->code . ' berhasil dihapus.');
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect()->route('penjualan.index')
                ->with('error', 'Terjadi kesalahan saat menghapus penjualan: ' . $th->getMessage());
        }
    }
}
