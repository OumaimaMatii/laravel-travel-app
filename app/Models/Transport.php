<?php
// app/Models/Transport.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'compagnie',
        'numero_vol',
        'depart',
        'arrivee',
        'ville_depart_id',      // ✅ AJOUTER
        'ville_arrivee_id',     // ✅ AJOUTER
        'heure_depart',
        'heure_arrivee',
        'prix',
        'places_disponibles',
        'places_reservees_temp',
        'type_transport_id'
    ];

    // Relation avec la ville de départ
    public function villeDepart()
    {
        return $this->belongsTo(Ville::class, 'ville_depart_id');
    }

    // Relation avec la ville d'arrivée
    public function villeArrivee()
    {
        return $this->belongsTo(Ville::class, 'ville_arrivee_id');
    }

    public function typeTransport()
    {
        return $this->belongsTo(TypeTransport::class);
    }

    public function voyages()
    {
        return $this->belongsToMany(Voyage::class, 'voyage_transport')
                    ->withPivot('ordre');
    }
}