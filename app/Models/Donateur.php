<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class Donateur extends Model
{
    protected $guarded = [];

    use HasFactory, HasRoles;
    public function groupe_sanguin()
    {
        return $this->belongsTo(Groupe_sanguin::class);
    }

    public function participations()
    {
        return $this->hasMany(Participation::class);
    }
    public function campagnes()
    {
        return $this->belongsToMany(Campagne::class, 'participations');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
