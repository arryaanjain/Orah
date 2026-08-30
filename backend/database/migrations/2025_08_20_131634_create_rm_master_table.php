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
        Schema::create('rm_master', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('material')->notNullable();
            $table->text('description')->nullable();
            $table->string('base_unit', 50)->notNullable(); // kg, liters, pieces, etc.
            $table->decimal('minimum_stock', 10, 3)->default(0);
            $table->timestamps();
            
            // Unique constraint and indexes
            $table->unique(['company_id', 'user_id', 'material'], 'unique_material');
            $table->index(['company_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rm_master');
    }
};
