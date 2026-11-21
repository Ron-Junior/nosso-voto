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
        Schema::table('weddings', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('wedding_vendors', function (Blueprint $table) {
            $table->index('wedding_id');
        });

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('pinterest_inspirations', function (Blueprint $table) {
            $table->index('wedding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('wedding_vendors', function (Blueprint $table) {
            $table->dropIndex(['wedding_id']);
        });

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('pinterest_inspirations', function (Blueprint $table) {
            $table->dropIndex(['wedding_id']);
        });
    }
};

