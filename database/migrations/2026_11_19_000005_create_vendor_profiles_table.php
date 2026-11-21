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
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('business_name');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->string('city')->index();
            $table->string('state', 2)->index();
            $table->string('whatsapp');
            $table->string('instagram')->nullable();
            $table->text('description')->nullable();
            $table->text('cover_image_url')->nullable();
            $table->decimal('price_from', 10, 2)->nullable();
            $table->boolean('is_regional_highlight')->default(false)->index();
            $table->timestamp('highlight_expires_at')->nullable();
            $table->timestamps();
        });

        Schema::table('wedding_vendors', function (Blueprint $table) {
            $table->foreign('vendor_profile_id')
                ->references('id')
                ->on('vendor_profiles')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wedding_vendors', function (Blueprint $table) {
            $table->dropForeign(['vendor_profile_id']);
        });

        Schema::dropIfExists('vendor_profiles');
    }
};
