<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Voyage extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'date_depart', 
        'date_retour', 
        'destination_id',
        'ville_depart_id',
        'type_voyage_id',
        'titre',
        'description'
    ];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function villeDepart()
    {
        return $this->belongsTo(Ville::class, 'ville_depart_id');
    }

    public function typeVoyage()
    {
        return $this->belongsTo(TypeVoyage::class);
    }

    public function forfait()
    {
        return $this->hasOne(VoyageForfait::class);
    }

    public function surMesure()
    {
        return $this->hasOne(VoyageSurMesure::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function transports()
    {
        return $this->belongsToMany(Transport::class, 'voyage_transport')
                    ->withPivot('ordre');
    }

    public function activites()
    {
        return $this->belongsToMany(Activite::class, 'voyage_activite');
    }

    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}