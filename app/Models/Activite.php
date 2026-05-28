<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activite extends Model
{
     use HasFactory;
    //
    protected $fillable = ['nom', 'description', 'prix', 'image', 'adapte_enfants', 'destination_id'];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function voyages()
    {
        return $this->belongsToMany(Voyage::class, 'voyage_activite');
    }

    public function medias()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}