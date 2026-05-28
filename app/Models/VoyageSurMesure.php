<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class VoyageSurMesure extends Model
{
    use HasFactory;
    protected $table = 'voyages_sur_mesure';
    protected $fillable = ['voyage_id', 'budget_estime', 'client_id', 'statut_sur_mesure_id'];

    public function voyage()
    {
        return $this->belongsTo(Voyage::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function statut()
    {
        return $this->belongsTo(StatutSurMesure::class, 'statut_sur_mesure_id');
    }
}