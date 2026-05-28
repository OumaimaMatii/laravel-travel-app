<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeForfait extends Model
{
    use HasFactory;
    
    protected $fillable = ['nom'];

    public function voyagesForfait()
    {
        return $this->hasMany(VoyageForfait::class, 'type_forfait_id');
    }
}