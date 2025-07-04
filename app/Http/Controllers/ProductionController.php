<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Production;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->input('date')) {
            $date = Carbon::parse($request->date);
        } else {
            $date = Carbon::tomorrow();
        }

        $jiranganFromOrders = 0.0;
        $predictedDirectSalesJirangan = 0.0;

        $orders = Order::with('details.product', 'customer')->whereDate('date', $date)->get();
        if ($date->isToday() || $date->isTomorrow()) {        
            if ($orders->isNotEmpty()) {
                foreach ($orders as $order) {
                    foreach ($order->details as $detail) {
                        $jiranganFromOrders += $detail->qty / $detail->product->produce_per_jirangan;
                    }
                }
            }

            $historicalDirectSalesJirangan = $this->getOptimizedHistoricalDirectSalesJirangan(15);            

            // Prediksi
            $alpha = 0.3; // Bisa disesuaikan
            $predictedDirectSalesJirangan = $this->simpleExponentialSmoothing($historicalDirectSalesJirangan, $alpha);
            
            if ($predictedDirectSalesJirangan < 0) {
                $predictedDirectSalesJirangan = 0;
            }            
        }

        $production = Production::whereDate('date', $date)->first();
        if ($production) {
            if ($date->isToday() || $date->isTomorrow()) {
                $production->orders = $jiranganFromOrders;
                $production->direct_sales = $predictedDirectSalesJirangan;

                if (!$production->is_customized) {
                    $production->total = round($jiranganFromOrders + $predictedDirectSalesJirangan);
                }

                $production->save();
            }
        } else {
            $production = Production::create([
                'orders' => $jiranganFromOrders,
                'direct_sales' => $predictedDirectSalesJirangan,
                'total' => round($jiranganFromOrders + $predictedDirectSalesJirangan),
                'date' => $date,
                'is_customized' => false
            ]);
        }

        return Inertia::render('Productions/Index', [
            'title' => 'Produksi',
            'production' => $production,
            'orders' => $orders,
            'filter' => [
                'date' => $date->toDateString()
            ]
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

            return redirect()->route('produksi.index', ['date' => $production->date])->with('success', 'Total produksi berhasil diperbarui.');
        } catch (\Throwable $th) {
            return redirect()->route('produksi.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui total produksi: ' . $th->getMessage())
                ->withInput();
        }
    }

    private function getOptimizedHistoricalDirectSalesJirangan(int $days): array
    {
        $historicalData = [];        
        $endDate = Carbon::yesterday()->endOfDay();
        $startDate = $endDate->copy()->subDays($days - 1)->startOfDay();

        // Total jirangan perhari dari penjualan
        $dailySalesJiranganMap = SaleDetail::select(
            DB::raw('DATE(sales.created_at) as sales_day'),
            DB::raw('SUM(
                    COALESCE(
                        sale_details.qty / NULLIF(products.produce_per_jirangan, 0),
                        0
                    )
                ) as total_jirangan_daily')
        )
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->where('products.produce_per_jirangan', '>', 0)
            ->groupBy(DB::raw('DATE(sales.created_at)'))
            ->orderBy('sales_day', 'ASC')
            ->pluck('total_jirangan_daily', 'sales_day')
            ->all();

        // Total jirangan perhari dari pesanan
        $dailyOrdersJiranganMap = OrderDetail::select(
            DB::raw('DATE(orders.date) as order_day'),
            DB::raw('SUM(
                    COALESCE(
                        order_details.qty / NULLIF(products.produce_per_jirangan, 0),
                        0
                    )
                ) as total_jirangan_daily')
        )
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.date', [$startDate, $endDate])
            ->where('products.produce_per_jirangan', '>', 0)
            ->whereNotNull('orders.picked_at')
            ->groupBy(DB::raw('DATE(orders.date)'))
            ->orderBy('order_day', 'ASC')
            ->pluck('total_jirangan_daily', 'order_day')
            ->all();        

        // Merge
        $currentDate = $startDate->copy();
        while ($currentDate->lessThanOrEqualTo(Carbon::yesterday()->startOfDay())) {
            $dateString = $currentDate->toDateString();
            
            $soldJirangan = $dailySalesJiranganMap[$dateString] ?? 0;    
            $orderedJirangan = $dailyOrdersJiranganMap[$dateString] ?? 0;            
            $dailyDirectSales = $soldJirangan - $orderedJirangan;
            
            $historicalData[] = max(0.0, $dailyDirectSales);

            $currentDate->addDay();
        }

        return $historicalData;
    }

    private function simpleExponentialSmoothing(array $data, float $alpha): float
    {
        $n = count($data);
        
        if ($n === 0) {
            return 0;
        }
        if ($n === 1) {
            return (float) $data[0];
        }
        
        $forecast = (float) $data[0];
        
        for ($i = 0; $i < $n; $i++) {
            // F_t+1 = alpha * Y_t + (1 - alpha) * F_t
            $forecast = ($alpha * $data[$i]) + ((1 - $alpha) * $forecast);
        }

        return $forecast;
    }
}
