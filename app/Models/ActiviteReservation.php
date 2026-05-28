<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActiviteReservation extends Model
{
    protected $table = 'activite_reservation';
    
    protected $fillable = [
        'reservation_id',
        'activite_id',
        'nb_adultes',
        'nb_enfants',
        'prix_unitaire_adulte',
        'prix_unitaire_enfant'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }
}