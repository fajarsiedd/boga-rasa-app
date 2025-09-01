<?php

namespace App\Observers;

use App\Models\Material;
use App\Models\Production;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductionObserver
{
    /**
     * Handle the Production "created" event.
     */
    public function created(Production $production): void
    {
        // if ($this->shouldAdjustStock($production->date)) {
        //     $this->adjustMaterialStock($production->total);
        // }
    }

    /**
     * Handle the Production "updated" event.
     */
    public function updated(Production $production): void
    {
        // if ($production->isDirty('total')) {
        //     $oldTotal = $production->getOriginal('total');
        //     $newTotal = $production->total;

        //     $changeInJirangan = $newTotal - $oldTotal;

        //     if ($this->shouldAdjustStock($production->date)) {
        //         $this->adjustMaterialStock($changeInJirangan);
        //     }
        // }
    }

    /**
     * Handle the Production "deleted" event.
     */
    public function deleted(Production $production): void
    {
        //
    }

    /**
     * Handle the Production "restored" event.
     */
    public function restored(Production $production): void
    {
        //
    }

    /**
     * Handle the Production "force deleted" event.
     */
    public function forceDeleted(Production $production): void
    {
        //
    }

    private function adjustMaterialStock(int $changeInJirangan)
    {
        DB::transaction(function () use ($changeInJirangan) {
            $materials = Material::where('measure_per_jirangan', '>', 0)->get();

            foreach ($materials as $material) {
                $materialUsage = $changeInJirangan * $material->measure_per_jirangan;

                $material->stock -= $materialUsage;
                $material->save();
            }
        });
    }

    private function shouldAdjustStock($productionDate): bool
    {
        $productionCarbonDate = Carbon::parse($productionDate)->startOfDay();
        $today = Carbon::today();
        
        return $productionCarbonDate->greaterThanOrEqualTo($today);
    }
}
