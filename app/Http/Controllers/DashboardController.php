<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail; // Asumsikan Anda memiliki model SaleDetail
use App\Models\Purchase; // Asumsikan Anda memiliki model Purchase
use App\Models\PurchaseDetail; // Asumsikan Anda memiliki model PurchaseDetail
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Untuk raw queries jika diperlukan, atau Aggregate functions

class DashboardController extends Controller
{
    public function index(Request $request)
    {        
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        if (!$startDate && !$endDate) {
            $currentPeriodStart = Carbon::now()->startOfMonth();
            $currentPeriodEnd = Carbon::now()->endOfMonth();
        } else {
            $currentPeriodStart = Carbon::parse($startDate)->startOfDay();
            $currentPeriodEnd = Carbon::parse($endDate)->endOfDay();
        }

        // Prev period
        $periodDuration = $currentPeriodStart->diffInDays($currentPeriodEnd) + 1;
        $previousPeriodEnd = $currentPeriodStart->copy()->subDay()->endOfDay();
        $previousPeriodStart = $previousPeriodEnd->copy()->subDays($periodDuration - 1)->startOfDay();


        // Helper to get statistic data
        $getPeriodStats = function (Carbon $start, Carbon $end) {            
            $totalSalesTransactions = Sale::whereBetween('created_at', [$start, $end])->count();
            $totalSalesRevenue = Sale::whereBetween('created_at', [$start, $end])
                                    ->sum(DB::raw('total'));
                                                
            $totalPurchasesTransactions = Purchase::whereBetween('created_at', [$start, $end])->count();
            $totalPurchasesExpenses = Purchase::whereBetween('created_at', [$start, $end])
                                    ->sum('total');
            return [
                'totalSalesTransactions' => $totalSalesTransactions,
                'totalSalesRevenue' => $totalSalesRevenue,
                'totalPurchasesTransactions' => $totalPurchasesTransactions,
                'totalPurchasesExpenses' => $totalPurchasesExpenses,
            ];
        };

        $currentStats = $getPeriodStats($currentPeriodStart, $currentPeriodEnd);
        $previousStats = $getPeriodStats($previousPeriodStart, $previousPeriodEnd);


        // Helper to get percentage change
        $calculatePercentageChange = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }
            return round((($current - $previous) / $previous) * 100, 2);
        };


        // Summary Cards
        $dashboardStats = [
            'sales' => [
                'currentPeriod' => [
                    'totalTransactions' => $currentStats['totalSalesTransactions'],
                    'totalRevenue' => $currentStats['totalSalesRevenue'],
                ],
                'previousPeriod' => [
                    'totalTransactions' => $previousStats['totalSalesTransactions'],
                    'totalRevenue' => $previousStats['totalSalesRevenue'],
                ],
                'transactionPercentageChange' => $calculatePercentageChange(
                    $currentStats['totalSalesTransactions'],
                    $previousStats['totalSalesTransactions']
                ),
                'revenuePercentageChange' => $calculatePercentageChange(
                    $currentStats['totalSalesRevenue'],
                    $previousStats['totalSalesRevenue']
                ),
            ],
            'purchases' => [
                'currentPeriod' => [
                    'totalTransactions' => $currentStats['totalPurchasesTransactions'],
                    'totalExpenses' => $currentStats['totalPurchasesExpenses'],
                ],
                'previousPeriod' => [
                    'totalTransactions' => $previousStats['totalPurchasesTransactions'],
                    'totalExpenses' => $previousStats['totalPurchasesExpenses'],
                ],
                'transactionPercentageChange' => $calculatePercentageChange(
                    $currentStats['totalPurchasesTransactions'],
                    $previousStats['totalPurchasesTransactions']
                ),
                'expensesPercentageChange' => $calculatePercentageChange(
                    $currentStats['totalPurchasesExpenses'],
                    $previousStats['totalPurchasesExpenses']
                ),
            ],
            'totalRevenue' => [
                'current' => $currentStats['totalSalesRevenue'],
                'previous' => $previousStats['totalSalesRevenue'],
                'percentageChange' => $calculatePercentageChange(
                    $currentStats['totalSalesRevenue'],
                    $previousStats['totalSalesRevenue']
                ),
            ],
            'totalExpenses' => [
                'current' => $currentStats['totalPurchasesExpenses'],
                'previous' => $previousStats['totalPurchasesExpenses'],
                'percentageChange' => $calculatePercentageChange(
                    $currentStats['totalPurchasesExpenses'],
                    $previousStats['totalPurchasesExpenses']
                ),
            ],
        ];

        // LineChart - Daily Sales
        $dailySales = Sale::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as daily_revenue')
            )
            ->whereBetween('created_at', [$currentPeriodStart->startOfDay(), $currentPeriodEnd->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $lineChartCategories = [];
        $lineChartSeriesData = [];
        
        $dateIterator = $currentPeriodStart->copy();
        while ($dateIterator->lte($currentPeriodEnd)) {
            $dateString = $dateIterator->toDateString();
            $lineChartCategories[] = $dateString;
            $lineChartSeriesData[] = $dailySales[$dateString]->daily_revenue ?? 0;
            $dateIterator->addDay();
        }

        $lineChartData = [
            'categories' => $lineChartCategories,
            'series' => [
                [
                    'name' => 'Pendapatan',
                    'data' => $lineChartSeriesData,
                ]
            ]
        ];

        // Pie Chart - Top Sold Products
        $topProducts = SaleDetail::select(
                'products.name as product_name',
                DB::raw('SUM(sale_details.qty) as total_quantity_sold')
            )
            ->join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->join('products', 'sale_details.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$currentPeriodStart->startOfDay(), $currentPeriodEnd->endOfDay()])
            ->groupBy('products.name')
            ->orderByDesc('total_quantity_sold')
            ->limit(5)
            ->get();

        $pieChartLabels = [];
        $pieChartSeries = [];
        $otherProductsQuantity = 0;

        $totalTopProductsQuantity = $topProducts->sum('total_quantity_sold');
        
        $totalAllProductsSold = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$currentPeriodStart->startOfDay(), $currentPeriodEnd->endOfDay()])
            ->sum('sale_details.qty');
        
        if ($totalAllProductsSold > 0) {
            foreach ($topProducts as $product) {
                $pieChartLabels[] = $product->product_name;                
                $pieChartSeries[] = round(($product->total_quantity_sold / $totalAllProductsSold) * 100, 2);
            }
            
            $otherProductsQuantity = $totalAllProductsSold - $totalTopProductsQuantity;
            if ($otherProductsQuantity > 0) {
                $pieChartLabels[] = 'Lainnya';
                $pieChartSeries[] = round(($otherProductsQuantity / $totalAllProductsSold) * 100, 2);
            }
        } else {             
             $pieChartLabels = ['Tidak ada penjualan'];
             $pieChartSeries = [100];
        }


        $pieChartData = [
            'labels' => $pieChartLabels,
            'series' => $pieChartSeries,
        ];
        
        return Inertia::render('Dashboard', [
            'title' => 'Dashboard',
            'dashboardStats' => $dashboardStats,
            'lineChartData' => $lineChartData,
            'pieChartData' => $pieChartData,
            'filter' => [
                'startDate' => $currentPeriodStart->toDateString(),
                'endDate' => $currentPeriodEnd->toDateString()
            ]
        ]);
    }
}