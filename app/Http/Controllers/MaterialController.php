<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view-materials');

        $historicalTotalProductionJirangan = $this->getHistoricalTotalProduction(15);        

        $alpha = 0.3; // Konstanta smoothing
        $predictedJirangan = $this->simpleExponentialSmoothing($historicalTotalProductionJirangan, $alpha);        

        if ($predictedJirangan < 0) {
            $predictedJirangan = 0;
        }

        $materialsQuery = Material::orderBy('name');

        if ($request->has('search') && $request->search != null) {
            $searchTerm = '%' . $request->search . '%';

            $materialsQuery->where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm);
            });
        }

        $materials = $materialsQuery->get();
        $today = Carbon::today();

        foreach ($materials as $material) {
            $currentStock = $material->stock;
            $usagePerJirangan = $material->measure_per_jirangan;

            $daysUntilDepletion = 0;

            if ($predictedJirangan <= 0 || $usagePerJirangan <= 0) {
                $material->estimated_depletion_date = 'N/A';
            } else {
                $dailyConsumption = $predictedJirangan * $usagePerJirangan;

                while ($currentStock > 0) {
                    $currentStock -= $dailyConsumption;
                    $daysUntilDepletion++;

                    if ($daysUntilDepletion > 365 * 5) {
                        $daysUntilDepletion = 'N/A';
                        break;
                    }
                }

                if ($material->estimated_depletion_date !== 'N/A') {
                    $material->estimated_depletion_date = Carbon::parse($today->copy()->addDays($daysUntilDepletion))->locale('id')->translatedFormat('d F Y');
                }
            }
        }

        return Inertia::render('Materials/Index', [
            'title' => 'Daftar Bahan Baku',
            'materials' => $materials
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create-material');

        return Inertia::render('Materials/Create', [
            'title' => 'Tambah Bahan Baku Baru'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create-material');

        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'measure_per_jirangan' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            Material::create([
                'name' => $request->name,
                'stock' => $request->stock,
                'measure_per_jirangan' => $request->measure_per_jirangan
            ]);

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku ' . $request->name . ' berhasil ditambahkan.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat menambahkan bahan baku: ' . $th->getMessage())
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
        $this->authorize('edit-material');

        $material = Material::find($id);

        return Inertia::render('Materials/Edit', [
            'title' => 'Edit Bahan Baku - ' . $material->name,
            'material' => $material
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->authorize('edit-material');

        $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'measure_per_jirangan' => 'required|integer|min:0'
        ]);

        DB::beginTransaction();

        try {
            $material = Material::find($id);
            $material->name = $request->name;
            $material->stock = $request->stock;
            $material->measure_per_jirangan = $request->measure_per_jirangan;
            $material->save();

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui bahan baku: ' . $th->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete-material');

        DB::beginTransaction();

        try {
            $material = Material::find($id);
            $material->delete();

            DB::commit();

            return redirect()->route('bahan-baku.index')->with('success', 'Bahan baku ' . $material->name . ' berhasil dihapus.');
        } catch (\Throwable $th) {
            DB::rollBack();

            return redirect()->route('bahan-baku.index')
                ->with('error', 'Terjadi kesalahan saat menghapus bahan baku: ' . $th->getMessage())
                ->withInput();
        }
    }

    private function getHistoricalTotalProduction(int $days): array
    {
        $historicalData = [];
        $endDate = Carbon::yesterday()->endOfDay();
        $startDate = $endDate->copy()->subDays($days - 1)->startOfDay();

        $dailyProductionMap = Production::select(DB::raw('DATE(date) as production_date'), 'total')
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'ASC')
            ->pluck('total', 'production_date')
            ->all();            

        $currentDate = $startDate->copy();
        while ($currentDate->lessThanOrEqualTo(Carbon::yesterday()->startOfDay())) {
            $dateString = $currentDate->toDateString();

            $totalProduction = $dailyProductionMap[$dateString] ?? 0;

            $historicalData[] = $totalProduction;
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
