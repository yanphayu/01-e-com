<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('twitter');
            $table->text('bio')->nullable()->after('username');
            $table->string('gender')->nullable()->after('bio');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('location')->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('location');
            $table->string('cover_photo')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'bio',
                'gender',
                'date_of_birth',
                'location',
                'address',
                'cover_photo',
            ]);
        });
    }
};
