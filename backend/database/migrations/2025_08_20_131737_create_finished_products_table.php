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
        Schema::create('finished_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('product_name')->notNullable();
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->default('pieces');
            $table->decimal('selling_price', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['company_id', 'user_id', 'product_name'], 'unique_product');
            $table->index(['company_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finished_products');
    }
};
