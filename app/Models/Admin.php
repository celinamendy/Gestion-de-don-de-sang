<?php

namespace App\Models;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Admin extends Model
{
    protected $guarded = [];

    use HasFactory, HasRoles;

    public function structures()
    {
        return $this->hasMany(Structure_transfusion_sanguine::class);
    }

    public function organisations()
    {
        return $this->hasMany(Organisateur::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
