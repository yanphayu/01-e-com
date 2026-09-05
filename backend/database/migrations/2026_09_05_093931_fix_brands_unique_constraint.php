<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique('brands_name_unique');
            $table->unique(['name', 'subcategory_id']);
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropUnique(['name', 'subcategory_id']);
            $table->unique('name');
        });
    }
};
