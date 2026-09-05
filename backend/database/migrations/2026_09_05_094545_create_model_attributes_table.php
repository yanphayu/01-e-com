<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('model_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('product_models')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['model_id', 'attribute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_attributes');
    }
};
