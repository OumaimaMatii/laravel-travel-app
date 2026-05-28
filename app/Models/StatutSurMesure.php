<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatutSurMesure extends Model
{
    use HasFactory;
    protected $table = 'statut_sur_mesure';
    protected $fillable = ['nom'];

    public function voyagesSurMesure()
    {
        return $this->hasMany(VoyageSurMesure::class);
    }
}