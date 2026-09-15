<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('replied_to')->nullable()->after('product_id')
                ->constrained('messages')->nullOnDelete();
            $table->boolean('is_pinned')->default(false)->after('replied_to');
            $table->boolean('forwarded')->default(false)->after('is_pinned');
            $table->timestamp('edited_at')->nullable()->after('forwarded');
            $table->timestamp('deleted_at')->nullable()->after('edited_at');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['replied_to']);
            $table->dropColumn(['replied_to', 'is_pinned', 'forwarded', 'edited_at', 'deleted_at']);
        });
    }
};
