<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTypeChambre extends Model
{
    //
    protected $table = 'hotel_type_chambre';

    protected $fillable = ['hotel_id', 'type_chambre_id', 'quantite_disponible', 'prix_par_nuit'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function typeChambre()
    {
        return $this->belongsTo(TypeChambre::class);
    }
}