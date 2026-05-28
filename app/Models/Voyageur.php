<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voyageur extends Model
{
    use HasFactory;
    protected $fillable = ['reservation_id', 'nom_complet', 'date_naissance', 'sexe', 'numero_passeport'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}