<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    protected $table = 'medias';
    protected $fillable = ['url', 'titre', 'ordre', 'est_principale', 'mediable_type', 'mediable_id'];

    public function mediable()
    {
        return $this->morphTo();
    }
}