<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->after('last_message_at');
            $table->boolean('is_muted')->default(false)->after('is_pinned');
            $table->boolean('is_archived')->default(false)->after('is_muted');
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn(['is_pinned', 'is_muted', 'is_archived']);
        });
    }
};
