<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutForfait extends Model
{
    use HasFactory;
    protected $table = 'statut_forfait';
    protected $fillable = ['nom'];

    public function voyagesForfait()
    {
        return $this->hasMany(VoyageForfait::class);
    }
}