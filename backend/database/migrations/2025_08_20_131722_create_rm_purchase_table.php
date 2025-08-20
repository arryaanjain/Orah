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
        Schema::create('rm_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('material_id')->references('id')->on('rm_master')->onDelete('cascade');
            $table->string('supplier_name')->nullable();
            $table->date('purchase_date')->notNullable();
            $table->decimal('qty', 10, 3)->notNullable();
            $table->foreignId('unit_id')->references('id')->on('rm_master_units')->onDelete('cascade');
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('batch_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'user_id', 'material_id'], 'idx_company_user_material');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_purchase');
    }
};
