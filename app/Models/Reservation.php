<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voyage_id',
        'nb_adultes',
        'nb_enfants',
        'date_reservation',
        'statut',
        'montant_total',
        'mode_paiement',
        'date_paiement',
        'confirmation_deadline',
        'notification_envoyee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voyage()
    {
        return $this->belongsTo(Voyage::class);
    }

    public function detailReservations()
    {
        return $this->hasMany(DetailReservation::class);
    }

    public function voyageurs()
    {
        return $this->hasMany(Voyageur::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    
    public function getTransportAttribute()
    {
        return $this->voyage?->transports()->first();
    }
}