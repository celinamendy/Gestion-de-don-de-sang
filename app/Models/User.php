<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function donateur()
{
    return $this->hasOne(Donateur::class);
}


public function organisateur()
{
    return $this->hasOne(Organisateur::class);
}

public function structure()
{
    return $this->hasOne(StructureTransfusionSanguin::class);
}

public function admin()
{
    return $this->hasOne(Admin::class);
}
public function region()
{
    return $this->belongsTo(Region::class);
}
public function notification()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    public function participations()
    {
        return $this->hasMany(Participation::class);
    }
     
}

