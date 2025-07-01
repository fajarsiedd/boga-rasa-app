<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Order;
use App\Models\Product;
use App\Models\Receivable;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-sales');

        $salesQuery = Sale::with('details.product', 'customer.customerType', 'receivable')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $salesQuery->where(function ($query) use ($searchTerm) {
                $query->where('code', 'like', $searchTerm)
                    ->orWhereHas('customer', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($request->has('date') && $request->date != null) {
            $salesQuery->whereDate('created_at', $request->date);
        }

        if ($request->has('status') && $request->status != null) {
            if ($request->status == 'lunas') {
                $salesQuery->whereNotNull('paid_at');
            } else if ($request->status == 'ditunda') {
                $salesQuery->whereNull('paid_at');
            }
        }

        $sales = $salesQuery->get();

        return Inertia::render('Sales/Index', [
            'title' => 'Daftar Transaksi Penjualan',
            'sales' => $sales            
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create-sale');

        $order = null;
        $orderId = $request->query('order_id');
        if ($orderId) {
            $order = Order::with('details.product', 'customer.customerType')->find($orderId);

            if (!$order) {
                return redirect()->route('penjualan.create')->with('error', 'Pesanan tidak ditemukan.');
            }
        }

        $customers = Customer::with('customerType')->orderBy('name')->get();
        $customerTypes = CustomerType::all();
        $products = Product::orderBy('name')->get();

        return Inertia::render('Sales/Create', [
            'title' => 'Buat Transaksi Penjualan',
            'order' => $order,
            'customers' => $customers,
            'customerTypes' => $customerTypes,
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
            $lastSale = Sale::orderBy('id', 'desc')->first();
            $nextCodeNum = 1;

            if ($lastSale && $lastSale->code) {
                $lastCodeNum = (int) substr($lastSale->code, 2);
                $nextCodeNum = $lastCodeNum + 1;
            }

            $code = 'S-' . str_pad($nextCodeNum, 4, '0', STR_PAD_LEFT);
            $total = 0;
            $saleDetails = [];

            foreach ($request->details as $detail) {
                $productId = $detail['product_id'];
                $qty = $detail['qty'];
                $finalPrice = $detail['final_price'];

                $subtotal = $qty * $finalPrice;

                $saleDetails[] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'final_price' => $finalPrice,
                    'subtotal' => $subtotal,
                ];

                $total += $subtotal;
            }

            $paidAtValue = $request->boolean('is_paid') ? Carbon::now() : null;

            $sale = Sale::create([
                'code' => $code,
                'customer_id' => $request->customer_id,
                'total' => $total,
                'paid_at' => $paidAtValue
            ]);

            foreach ($saleDetails as $detail) {
                $sale->details()->create($detail);
            }

            $orderId = $request->query('order_id');
            $order = null;
            if ($orderId) {
                $order = Order::find($orderId);

                if (!$order) {
                    return redirect()->route('penjualan.create')->with('error', 'Pesanan tidak ditemukan.');
                }

                $order->picked_at = Carbon::now();
                $order->save();
            }

            if (!$paidAtValue) {
                Receivable::create([
                    'sale_id' => $sale->id,
                    'due_date' => $request->due_date
                ]);
            }

            DB::commit();

            if ($orderId) {
                return redirect()->route('pesanan.index')->with('success', 'Pesanan ' . $order->code . ' berhasil diselesaikan.');
            } else {
                return redirect()->route('penjualan.index')->with('success', 'Penjualan ' . $code . ' berhasil dibuat.');
            }
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('penjualan.index')
                ->with('error', 'Terjadi kesalahan saat membuat penjualan: ' . $th->getMessage())
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
        $customers = Customer::with('customerType')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $customerTypes = CustomerType::all();

        $receivable = Receivable::where('sale_id', $id)->first();
        if ($receivable) {
            $receivable->due_date = Carbon::parse($receivable->due_date)->setTimezone(config('app.timezone'))->format('Y-m-d');
        }

        return Inertia::render('Sales/Edit', [
            'title' => 'Edit Transaksi Penjualan - ' . $sale->code,
            'sale' => $sale,
            'receivable' => $receivable,
            'customers' => $customers,
            'products' => $products,
            'customerTypes' => $customerTypes
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
            'details.*.final_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::find($id);
            $total = 0;
            $saleDetails = [];

            foreach ($request->details as $detail) {
                $productId = $detail['product_id'];
                $qty = $detail['qty'];
                $finalPrice = $detail['final_price'];

                $subtotal = $qty * $finalPrice;

                $saleDetails[] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'final_price' => $finalPrice,
                    'subtotal' => $subtotal,
                ];

                $total += $subtotal;
            }

            $paidAtValue = $request->boolean('is_paid') ? Carbon::now() : null;

            $sale->update([
                'customer_id' => $request->customer_id,
                'paid_at' => $paidAtValue,
                'total' => $total,
            ]);

            $sale->details()->delete();

            foreach ($saleDetails as $detail) {
                $sale->details()->create($detail);
            }

            if (!$paidAtValue) {
                $receivable = Receivable::where('sale_id', $sale->id)->first();

                if (!$receivable) {
                    Receivable::create([
                        'sale_id' => $sale->id,
                        'due_date' => $request->due_date
                    ]);
                }
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
            $sale->receivable()->delete();
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
