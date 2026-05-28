<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoyageTransport extends Model
{
    //
    protected $table = 'voyage_transport';

    protected $fillable = ['voyage_id', 'transport_id', 'ordre'];

    public function voyage()
    {
        return $this->belongsTo(Voyage::class);
    }

    public function transport()
    {
        return $this->belongsTo(Transport::class);
    }
}