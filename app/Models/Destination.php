<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
   use HasFactory;
    protected $fillable = ['nom', 'pays', 'image_couverture', 'actif', 'ville_id'];

    public function ville()
    {
        return $this->belongsTo(Ville::class);
    }

    public function voyages()
    {
        return $this->hasMany(Voyage::class);
    }

    public function activites()
    {
        return $this->hasMany(Activite::class);
    }

    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}