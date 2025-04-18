<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $guarded = [];

    public function structures()
    {
        return $this->hasMany(Structure_transfusion_sanguine::class);
    }

 

}
