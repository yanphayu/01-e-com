<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->foreignId('model_id')->nullable()->after('brand_id')->constrained('product_models')->nullOnDelete();
            $table->dropColumn(['brand', 'model']);
        });
    }

    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('product_id');
            $table->string('model')->nullable()->after('brand');
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['model_id']);
            $table->dropColumn(['brand_id', 'model_id']);
        });
    }
};
