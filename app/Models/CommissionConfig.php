<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionConfig extends Model
{
    protected $fillable = ['type', 'pourcentage', 'actif', 'updated_by'];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function getPourcentage()
    {
        $config = self::where('type', 'sur_mesure')->where('actif', true)->first();
        return $config ? $config->pourcentage : 15.00;
    }
}