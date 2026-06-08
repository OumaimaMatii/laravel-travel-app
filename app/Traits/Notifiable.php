<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

trait Notifiable
{
    public function sendNotification($userId, $titre, $message, $type = 'info', $lien = null)
    {
        try {
            $notification = Notification::create([
                'user_id' => $userId,
                'titre' => $titre,
                'message' => $message,
                'type' => $type,
                'lien' => $lien,
                'envoyee_le' => now(),
            ]);
            
            Log::info("Notification cree pour l'utilisateur {$userId}: {$titre}");
            return $notification;
            
        } catch (\Exception $e) {
            Log::error("Erreur lors de la creation de notification pour l'utilisateur {$userId}: " . $e->getMessage());
            return null;
        }
    }

    public function sendNotificationToMany($userIds, $titre, $message, $type = 'info', $lien = null)
    {
        if (empty($userIds)) {
            return false;
        }
        
        $notifications = [];
        $now = now();
        
        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'titre' => $titre,
                'message' => $message,
                'type' => $type,
                'lien' => $lien,
                'envoyee_le' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        try {
            Notification::insert($notifications);
            Log::info("Notifications envoyees a " . count($notifications) . " utilisateurs: {$titre}");
            return true;
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi group de notifications: " . $e->getMessage());
            return false;
        }
    }

    public function notifyAdmins($titre, $message, $type = 'info', $lien = null)
    {
        $adminAndAgentIds = User::whereIn('role', ['admin', 'agent'])
            ->pluck('id')
            ->toArray();
        
        if (empty($adminAndAgentIds)) {
            Log::warning("Aucun admin ou agent trouve pour la notification: {$titre}");
            return false;
        }
        
        Log::info("Envoi de notification a " . count($adminAndAgentIds) . " administrateurs et agents");
        
        return $this->sendNotificationToMany($adminAndAgentIds, $titre, $message, $type, $lien);
    }
    
    public function notifyAgent($agentId, $titre, $message, $type = 'info', $lien = null)
    {
        if (!$agentId) {
            return false;
        }
        
        return $this->sendNotification($agentId, $titre, $message, $type, $lien);
    }
}