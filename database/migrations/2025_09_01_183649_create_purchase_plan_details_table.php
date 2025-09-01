<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_plan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_plan_id')->constrained('purchase_plans');            
            $table->foreignId('material_id')->constrained('materials');
            $table->integer('qty');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_plan_details');
    }
};
