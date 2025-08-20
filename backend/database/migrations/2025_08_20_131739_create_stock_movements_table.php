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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->references('id')->on('rm_master')->onDelete('cascade');
            $table->enum('movement_type', ['purchase', 'consumption', 'adjustment', 'return'])->notNullable();
            $table->enum('reference_type', ['purchase', 'sale', 'production', 'manual'])->notNullable();
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the related record
            $table->decimal('qty_change', 10, 3)->notNullable(); // positive for inbound, negative for outbound
            $table->foreignId('unit_id')->references('id')->on('rm_master_units')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamp('movement_date')->useCurrent();
            $table->timestamps();
            
            // Indexes
            $table->index(['material_id', 'movement_date'], 'idx_material_date');
            $table->index(['company_id', 'user_id'], 'idx_company_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
