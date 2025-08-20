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
        Schema::create('order_book', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->references('id')->on('finished_products')->onDelete('cascade');
            $table->decimal('qty', 10, 3)->notNullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->date('order_date')->notNullable();
            $table->date('expected_delivery_date')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'in_production', 'ready', 'delivered', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'user_id'], 'idx_company_user');
            $table->index(['order_date'], 'idx_order_date');
            $table->index(['status'], 'idx_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_book');
    }
};
