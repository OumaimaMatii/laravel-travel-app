<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'reservation_id', 'uploaded_by', 'titre', 'type', 
        'chemin_fichier', 'nom_fichier_original', 'taille'
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}