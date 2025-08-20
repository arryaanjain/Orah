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
        Schema::create('rm_master_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->references('id')->on('rm_master')->onDelete('cascade');
            $table->string('unit_name', 50)->notNullable();
            $table->decimal('conversion_factor', 10, 4)->default(1); // conversion to base unit
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['company_id', 'user_id', 'material_id', 'unit_name'], 'unique_material_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_master_units');
    }
};
