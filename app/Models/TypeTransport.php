<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeTransport extends Model
{
    use HasFactory;
    protected $fillable = ['nom'];

    public function transports()
    {
        return $this->hasMany(Transport::class);
    }
}