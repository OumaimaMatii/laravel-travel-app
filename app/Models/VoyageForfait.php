<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoyageForfait extends Model
{
    use HasFactory;
    
    protected $table = 'voyages_forfait';
    
    protected $fillable = [
        'voyage_id', 
        'prix_adulte', 
        'prix_enfant', 
        'hotel_id', 
        'statut_forfait_id', 
        'programme', 
        'nombre_places', 
        'places_restantes', 
        'agent_id',
        'type_forfait_id',
    ];

    public function voyage()
    {
        return $this->belongsTo(Voyage::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function statut()
    {
        return $this->belongsTo(StatutForfait::class, 'statut_forfait_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function typeForfait()
    {
        return $this->belongsTo(TypeForfait::class, 'type_forfait_id');
    }

    public function hasEnoughPlaces(int $nombrePersonnes): bool
    {
        return $this->places_restantes >= $nombrePersonnes;
    }

    public function reservePlaces(int $nombrePersonnes): void
    {
        $this->decrement('places_restantes', $nombrePersonnes);
    }

    public function annulePlaces(int $nombrePersonnes): void
    {
        $this->increment('places_restantes', $nombrePersonnes);
    }

    public function getPrixTotal(int $adultes, int $enfants = 0): float
    {
        $prixEnfant = $this->prix_enfant ?? $this->prix_adulte * 0.5;
        return ($adultes * $this->prix_adulte) + ($enfants * $prixEnfant);
    }
}