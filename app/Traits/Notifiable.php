<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;

trait Notifiable
{
    public function sendNotification($userId, $titre, $message, $type = 'info', $lien = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'titre' => $titre,
            'message' => $message,
            'type' => $type,
            'lien' => $lien,
            'envoyee_le' => now(),
        ]);
    }

    public function sendNotificationToMany($userIds, $titre, $message, $type = 'info', $lien = null)
    {
        $notifications = [];
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'titre' => $titre,
                'message' => $message,
                'type' => $type,
                'lien' => $lien,
                'envoyee_le' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        return Notification::insert($notifications);
    }

    public function notifyAdmins($titre, $message, $type = 'info', $lien = null)
    {
        $adminIds = User::whereIn('role', ['admin', 'agent'])->pluck('id');
        return $this->sendNotificationToMany($adminIds, $titre, $message, $type, $lien);
    }
}