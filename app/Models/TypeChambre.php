<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeChambre extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'capacite_max'];

    public function hotels()
    {
        return $this->belongsToMany(Hotel::class, 'hotel_type_chambre')
                    ->withPivot('quantite_disponible', 'prix_par_nuit');
    }

    public function detailReservations()
    {
        return $this->hasMany(DetailReservation::class);
    }
}