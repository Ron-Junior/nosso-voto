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
        Schema::create('banner_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('banner_cycle_id')->constrained('banner_cycles')->cascadeOnDelete();
            $table->foreignId('vendor_profile_id')->constrained('vendor_profiles')->cascadeOnDelete();
            $table->string('headline');
            $table->text('banner_image_url')->nullable();
            $table->string('cta_link')->nullable();
            $table->string('status')->default('pending')->index();
            $table->integer('slot_number')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->unique(['banner_cycle_id', 'vendor_profile_id']);
            $table->index(['banner_cycle_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banner_submissions');
    }
};
