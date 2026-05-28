<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 'titre', 'message', 'type', 'lien', 'lue', 'lue_le', 'envoyee_le'
    ];

    protected $casts = [
        'lue' => 'boolean',
        'lue_le' => 'datetime',
        'envoyee_le' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update([
            'lue' => true,
            'lue_le' => now(),
        ]);
    }

    public function scopeUnread($query)
    {
        return $query->where('lue', false);
    }
}