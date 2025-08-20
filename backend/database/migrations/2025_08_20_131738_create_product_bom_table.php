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
        Schema::create('product_bom', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->references('id')->on('finished_products')->onDelete('cascade');
            $table->foreignId('material_id')->references('id')->on('rm_master')->onDelete('cascade');
            $table->decimal('qty_required', 10, 4)->notNullable(); // quantity per unit of finished product
            $table->foreignId('unit_id')->references('id')->on('rm_master_units')->onDelete('cascade');
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['company_id', 'user_id', 'product_id', 'material_id'], 'unique_product_material');
            $table->index(['product_id'], 'idx_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bom');
    }
};
