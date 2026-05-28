<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;
    protected $fillable = ['nom', 'adresse', 'etoiles', 'image_principale', 'ville_id'];

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function typeChambres()
    {
        return $this->belongsToMany(TypeChambre::class, 'hotel_type_chambre')
                    ->withPivot('quantite_disponible', 'prix_par_nuit');
    }

    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}