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
        Schema::create('showtime_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showtime_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['regular', 'vip'])->default('regular');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('showtime_prices');
    }
};
