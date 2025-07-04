<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-orders');

        $ordersQuery = Order::with('customer.customerType', 'details.product')->orderBy('created_at', 'desc');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $ordersQuery->where(function ($query) use ($searchTerm) {
                $query->where('code', 'like', $searchTerm)
                    ->orWhereHas('customer', function ($query) use ($searchTerm) {
                        $query->where('name', 'like', $searchTerm);
                    });
            });
        }

        if ($request->has('date') && $request->date != null) {
            $ordersQuery->whereDate('date', $request->date);
        }

        if ($request->has('status') && $request->status != null) {
            if ($request->status == 'selesai') {
                $ordersQuery->whereNotNull('picked_at');
            } else if ($request->status == 'belum') {
                $ordersQuery->whereNull('picked_at');
            }
        }

        $orders = $ordersQuery->get();

        return Inertia::render('Orders/Index', [
            'title' => 'Daftar Pesanan',
            'orders' => $orders
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-order');

        $customers = Customer::with('customerType')->orderBy('name')->get();
        $customerTypes = CustomerType::all();
        $products = Product::orderBy('name')->get();

        return Inertia::render('Orders/Create', [
            'title' => 'Tambah Pesanan Baru',
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
        $this->authorize('create-order');

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $lastOrder = Order::orderBy('id', 'desc')->first();
            $nextCodeNum = 1;

            if ($lastOrder && $lastOrder->code) {
                $lastCodeNum = (int) substr($lastOrder->code, 2);
                $nextCodeNum = $lastCodeNum + 1;
            }

            $code = 'O-' . str_pad($nextCodeNum, 4, '0', STR_PAD_LEFT);

            $order = Order::create([
                'code' => $code,
                'customer_id' => $request->customer_id,
                'date' => $request->date
            ]);

            foreach ($request->details as $detail) {
                $order->details()->create($detail);
            }

            DB::commit();

            return redirect()->route('pesanan.index')->with('success', 'Pesanan ' . $code . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('pesanan.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan pesanan: ' . $th->getMessage())
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
        $this->authorize('edit-order');

        $order = Order::with('details.product', 'customer')->find($id);
        $order->date = Carbon::parse($order->date)->setTimezone(config('app.timezone'))->format('Y-m-d');

        $customers = Customer::with('customerType')->orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $customerTypes = CustomerType::all();

        return Inertia::render('Orders/Edit', [
            'title' => 'Edit Pesanan - ' . $order->code,
            'order' => $order,
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
        $this->authorize('edit-order');

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date' => 'required|date',
            'details' => 'required|array|min:1',
            'details.*.product_id' => 'required|exists:products,id',
            'details.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $order = Order::with('details')->find($id);

            if ($order->picked_at) {
                return redirect()->route('pesanan.index')
                    ->with('error', 'Pesanan sudah diambil.')
                    ->withInput();
            }

            $order->update([
                'customer_id' => $request->customer_id,
                'date' => $request->date
            ]);

            $order->details()->delete();

            foreach ($request->details as $detail) {
                $order->details()->create($detail);
            }

            DB::commit();

            return redirect()->route('pesanan.index')->with('success', 'Pesanan ' . $order->code . ' berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('pesanan.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui pesanan: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-order');

        DB::beginTransaction();

        try {
            $order = Order::find($id);
            $order->details()->delete();
            $order->delete();

            DB::commit();

            return redirect()->route('pesanan.index')
                ->with('success', 'Pesanan ' . $order->code . ' berhasil dihapus.');
        } catch (\Exception $th) {
            DB::rollBack();

            return redirect()->route('pesanan.index')
                ->with('error', 'Terjadi kesalahan saat menghapus pesanan: ' . $th->getMessage());
        }
    }
}
