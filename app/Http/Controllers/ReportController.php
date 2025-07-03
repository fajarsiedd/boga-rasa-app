<?php

namespace App\Http\Controllers;

use App\Models\Production;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Receivable;
use App\Models\Sale;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedYear = $request->input('year', Carbon::now()->year);
        $selectedMonth = $request->input('month', Carbon::now()->month);

        $periodStart = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth();
        $periodEnd = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth();

        // --- 1. Rekap Penjualan (dengan detail harian dan total produksi) ---
        // Menggabungkan data penjualan harian dengan produksi harian
        $dailySales = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(id) as total_transactions'),
            DB::raw('SUM(total) as total_revenue')
        )
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $dailyProductions = Production::select(
            DB::raw('DATE(date) as date'),
            DB::raw('SUM(total) as total_produced_quantity')
        )
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->keyBy('date'); // Mengubah koleksi menjadi associative array berdasarkan tanggal

        $salesRecap = [];
        $grandTotalSaleTransactions = 0;
        $grandTotalRevenue = 0;
        $grandTotalProduction = 0;

        // --- BAGIAN INI YANG DIMODIFIKASI ---
        // Iterate hanya melalui tanggal-tanggal yang memiliki data penjualan
        foreach ($dailySales as $sale) {
            $dateString = $sale->date; // Tanggal sudah dalam format YYYY-MM-DD dari query $dailySales

            // Cari data produksi yang sesuai untuk tanggal ini
            $production = $dailyProductions->get($dateString);

            // Ambil data transaksi dan pendapatan dari objek $sale
            $transactions = $sale->total_transactions;
            $revenue = $sale->total_revenue;

            // Ambil data produksi, jika tidak ada, set ke 0
            $producedQuantity = $production ? $production->total_produced_quantity : 0;

            $salesRecap[] = [
                'date' => Carbon::parse($dateString)->locale('id')->translatedFormat('d F Y'), // Format tanggal untuk tampilan
                'total_transactions' => $transactions,
                'total_revenue' => $revenue,
                'total_production' => $producedQuantity,
            ];

            $grandTotalSaleTransactions += $transactions;
            $grandTotalRevenue += $revenue;
            $grandTotalProduction += $producedQuantity;
        }

        // --- 2. Detail Produk Terjual ---
        $grandTotalProductsSold = 0;
        $grandTotalProductionProductsSold = 0;

        $detailProductsSold = SaleDetail::select(
            'products.name as product_name',
            DB::raw('SUM(sale_details.qty) as total_quantity_sold'),
            DB::raw('COALESCE(SUM(sale_details.qty) / NULLIF(products.produce_per_jirangan, 0), 0) as total_production')
        )
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->groupBy('products.name', 'products.produce_per_jirangan')
            ->orderBy('total_quantity_sold', 'DESC')
            ->get();

        foreach ($detailProductsSold as $soldProduct) {
            $grandTotalProductsSold += $soldProduct->total_quantity_sold;
            $grandTotalProductionProductsSold += $soldProduct->total_production;
        }

        // --- 3. Rekap Pembelian (detail harian) ---
        $grandTotalPurchaseTransactions = 0;
        $grandTotalExpenses = 0;

        $purchasesRecap = Purchase::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(id) as total_transactions'),
            DB::raw('SUM(total) as total_expenses')
        )
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get()
            ->map(function ($purchase) {
                $purchase->date = Carbon::parse($purchase->date)->locale('id')->translatedFormat('d F Y');
                return $purchase;
            });

        foreach ($purchasesRecap as $purchase) {
            $grandTotalPurchaseTransactions += $purchase->total_transactions;
            $grandTotalExpenses += $purchase->total_expenses;
        }

        // --- 4. Detail Pembelian Bahan Baku ---        
        $detailPurchasedRawMaterials = PurchaseDetail::select(
            'materials.name as material_name',
            DB::raw('SUM(purchase_details.qty) as total_quantity_purchased')
        )
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->join('materials', 'purchase_details.material_id', '=', 'materials.id')
            ->whereBetween('purchases.created_at', [$periodStart, $periodEnd])
            ->groupBy('materials.name')
            ->orderBy('total_quantity_purchased', 'DESC')
            ->get();

        // --- 5. Piutang Aktif ---
        $grandTotalAmount = 0;

        $activeReceivablesDetails = Sale::with(['customer', 'details.product', 'receivable'])
            // Lakukan JOIN dengan tabel 'customers' untuk bisa mengakses kolom 'name'
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->whereBetween('sales.created_at', [$periodStart, $periodEnd])
            ->whereNull('sales.paid_at')
            ->has('receivable')
            ->orderBy('customers.name', 'ASC')
            ->select('sales.*')
            ->get()
            ->map(function ($sale) {
                return [
                    'customer_name' => $sale->customer->name,
                    'details' => $sale->details,
                    'outstanding_amount' => $sale->total, // Total penjualan adalah nominal piutang jika belum lunas
                    'due_date' => Carbon::parse($sale->receivable->due_date)->locale('id')->translatedFormat('d F Y') ?? 'N/A',
                ];
            });

        foreach ($activeReceivablesDetails as $receivable) {
            $grandTotalAmount +=  $receivable['outstanding_amount'];
        }

        return Inertia::render('Reports/Index', [
            'title' => 'Laporan Bulanan',
            'filter' => [
                'year' => $selectedYear,
                'month' => str_pad($selectedMonth, 2, '0', STR_PAD_LEFT),
            ],
            'reportData' => [
                'salesRecap' => [
                    'data' => $salesRecap,
                    'grandTotalSaleTransactions' => $grandTotalSaleTransactions,
                    'grandTotalRevenue' => $grandTotalRevenue,
                    'grandTotalProduction' => $grandTotalProduction
                ],
                'detailProductsSold' => [
                    'data' => $detailProductsSold,
                    'grandTotalProductsSold' => $grandTotalProductsSold,
                    'grandTotalProductionProductsSold' => $grandTotalProductionProductsSold
                ],
                'purchasesRecap' => [
                    'data' => $purchasesRecap,
                    'grandTotalPurchaseTransactions' => $grandTotalPurchaseTransactions,
                    'grandTotalExpenses' => $grandTotalExpenses
                ],
                'detailPurchasedRawMaterials' => $detailPurchasedRawMaterials,
                'activeReceivables' => [
                    'data' => $activeReceivablesDetails,
                    'grandTotalAmount' => $grandTotalAmount
                ]
            ],
        ]);
    }
}
