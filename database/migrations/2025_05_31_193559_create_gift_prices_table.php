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
        Schema::create('gift_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->timestamp('checked_at');
            $table->timestamps();

            // Индексы для быстрого поиска
            $table->index(['gift_id', 'checked_at']);
            $table->index('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gift_prices');
    }
}; 