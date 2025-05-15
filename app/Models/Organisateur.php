<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class Organisateur extends Model
{
    protected $guarded = [];
    use HasFactory, HasRoles;
    // Organisateur appartient à une campagne

    public function campagnes()
    {
        return $this->hasMany(Campagne::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function structure()
    {
        return $this->hasOne(StructureTransfusionSanguin::class, 'user_id', 'user_id');
    }
 
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

}
