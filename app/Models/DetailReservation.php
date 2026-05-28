<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailReservation extends Model
{
    use HasFactory;
    protected $table = 'detail_reservations';
    protected $fillable = ['reservation_id', 'hotel_id', 'type_chambre_id', 'quantite', 'prix_unitaire'];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function typeChambre()
    {
        return $this->belongsTo(TypeChambre::class);
    }
}