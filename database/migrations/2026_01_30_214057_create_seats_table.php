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
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hall_id')->constrained('halls')->onDelete('cascade');
            $table->char('row_number', 3); // Assuming rows can be labeled with up to 3 characters (e.g., 'A', 'AA', 'AAA')
            $table->integer('column_number');
            $table->enum('type', ['regular', 'vip'])->default('regular');
            $table->enum('status', ['available', 'out_of_service'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
