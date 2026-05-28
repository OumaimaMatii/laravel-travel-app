<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoyageActivite extends Model
{
    //
    protected $table = 'voyage_activite';

    protected $fillable = ['voyage_id', 'activite_id'];

    public function voyage()
    {
        return $this->belongsTo(Voyage::class);
    }

    public function activite()
    {
        return $this->belongsTo(Activite::class);
    }
}