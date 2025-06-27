<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductionController extends Controller
{
    public function index()
    {
        $jiranganFromOrders = 0;
        // $jiranganFromDirectSales = 0;

        $orders = Order::with('details.product', 'customer')->whereDate('date', Carbon::tomorrow())->get();
        if ($orders->isNotEmpty()) {
            foreach ($orders as $order) {
                foreach ($order->details as $detail) {
                    $jiranganFromOrders += $detail->qty / $detail->product->produce_per_jirangan;
                }
            }
        }

        $production = Production::whereDate('date', Carbon::tomorrow())->first();
        if ($production) {
            $production->orders = $jiranganFromOrders;
            $production->direct_sales = 0;
            
            if (!$production->is_customized) {
                $production->total = round($jiranganFromOrders + 0);
            }

            $production->save();
        } else {
            $production = Production::create([
                'orders' => $jiranganFromOrders,
                'direct_sales' => 0,
                'total' => round($jiranganFromOrders + 0),
                'date' => Carbon::tomorrow(),
                'is_customized' => false
            ]);
        }

        return Inertia::render('Productions/Index', [
            'title' => 'Produksi',
            'production' => $production,
            'orders' => $orders
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {            
            $production = Production::find($id);            

            if ($request->boolean('refresh')) {
                $production->is_customized = false;
            } else {
                $production->total = $request->total;
                $production->is_customized = true;
            }

            $production->save();

            return redirect()->route('produksi.index')->with('success', 'Total produksi berhasil diperbarui.');
        } catch (\Throwable $th) {
            return redirect()->route('produksi.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui total produksi: ' . $th->getMessage())
                ->withInput();
        }
    }
}
