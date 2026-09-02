<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'phone', 'birth_date', 'address', 'latitude', 'longitude', 'avatar', 'facebook', 'instagram', 'twitter'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function otps()
    {
        return $this->hasMany(Otp::class);
    }
}
