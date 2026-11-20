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
        Schema::create('wedding_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->nullable()->constrained('weddings')->cascadeOnDelete();
            $table->unsignedBigInteger('vendor_profile_id')->nullable()->index();
            $table->string('created_by_side')->default('couple');
            $table->string('vendor_name');
            $table->string('couple_names')->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_city')->nullable();
            $table->string('event_venue')->nullable();
            $table->string('category')->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->decimal('estimated_amount', 12, 2)->default(0);
            $table->decimal('contracted_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->string('status')->default('cotando')->index();
            $table->text('notes')->nullable();
            $table->string('invite_token', 64)->unique();
            $table->boolean('is_pinterest_authorized')->default(true);
            $table->boolean('has_paid_pinterest_unlock')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wedding_vendors');
    }
};
