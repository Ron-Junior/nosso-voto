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
        Schema::create('banner_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('city')->index();
            $table->string('state', 2)->index();
            $table->integer('duration_months')->default(2);
            $table->integer('max_slots')->default(5);
            $table->decimal('price', 10, 2)->default(149.90);
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['state', 'city', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_cycles');
    }
};
