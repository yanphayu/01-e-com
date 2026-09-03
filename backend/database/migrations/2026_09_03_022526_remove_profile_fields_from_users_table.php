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
        // First, migrate existing data to profiles table
        $users = DB::table('users')
            ->whereNotNull('phone')
            ->orWhereNotNull('birth_date')
            ->orWhereNotNull('avatar')
            ->orWhereNotNull('facebook')
            ->orWhereNotNull('instagram')
            ->orWhereNotNull('twitter')
            ->get();

        foreach ($users as $user) {
            $profileId = DB::table('profiles')->insertGetId([
                'user_id' => $user->id,
                'phone' => $user->phone,
                'birth_date' => $user->birth_date,
                'avatar' => $user->avatar,
                'facebook' => $user->facebook,
                'instagram' => $user->instagram,
                'twitter' => $user->twitter,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            // Migrate address data if exists
            if ($user->address || $user->latitude || $user->longitude) {
                DB::table('addresses')->insert([
                    'profile_id' => $profileId,
                    'address' => $user->address,
                    'latitude' => $user->latitude,
                    'longitude' => $user->longitude,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]);
            }
        }

        // Then drop the columns from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'birth_date',
                'address',
                'latitude',
                'longitude',
                'avatar',
                'facebook',
                'instagram',
                'twitter',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('avatar')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
        });

        // Migrate data back from profiles to users
        $profiles = DB::table('profiles')->get();
        foreach ($profiles as $profile) {
            DB::table('users')
                ->where('id', $profile->user_id)
                ->update([
                    'phone' => $profile->phone,
                    'birth_date' => $profile->birth_date,
                    'avatar' => $profile->avatar,
                    'facebook' => $profile->facebook,
                    'instagram' => $profile->instagram,
                    'twitter' => $profile->twitter,
                ]);

            // Migrate address back
            $address = DB::table('addresses')->where('profile_id', $profile->id)->first();
            if ($address) {
                DB::table('users')
                    ->where('id', $profile->user_id)
                    ->update([
                        'address' => $address->address,
                        'latitude' => $address->latitude,
                        'longitude' => $address->longitude,
                    ]);
            }
        }

        DB::table('addresses')->delete();
        DB::table('profiles')->delete();
    }
};
