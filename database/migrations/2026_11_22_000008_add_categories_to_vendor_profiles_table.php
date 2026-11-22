<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->jsonb('categories')->default('[]')->after('category');
        });

        DB::statement("UPDATE vendor_profiles SET categories = jsonb_build_array(category) WHERE categories = '[]'::jsonb AND category IS NOT NULL AND category <> ''");
        DB::statement('CREATE INDEX vendor_profiles_categories_gin_index ON vendor_profiles USING GIN (categories)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS vendor_profiles_categories_gin_index');

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $table->dropColumn('categories');
        });
    }
};
