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
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('paywall_pinterest_inspirations_enabled')->default(false);
            $table->decimal('platform_gift_fee_percent', 5, 2)->default(4.99);
            $table->decimal('regional_highlight_monthly_price', 10, 2)->default(69.90);
            $table->decimal('portfolio_monthly_price', 10, 2)->default(39.90);
            $table->integer('banner_cycle_months')->default(2);
            $table->integer('banner_max_slots')->default(5);
            $table->decimal('banner_cycle_price', 10, 2)->default(149.90);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};
