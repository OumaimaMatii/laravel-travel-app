<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommissionConfig;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    // GET /commission - Retourne le pourcentage de commission actuel
    public function getPourcentage(Request $request)
    {
        $pourcentage = CommissionConfig::getPourcentage();
        
        return response()->json([
            'success' => true,
            'pourcentage' => $pourcentage
        ]);
    }

    // PUT /commission - Met à jour le pourcentage de commission (admin uniquement)
    public function updatePourcentage(Request $request)
    {
        $user = $request->user();
        
        if (!$user || $user->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $request->validate([
            'pourcentage' => 'required|numeric|min:0|max:100'
        ]);

        $commission = CommissionConfig::where('type', 'sur_mesure')->first();
        
        if ($commission) {
            $commission->update([
                'pourcentage' => $request->pourcentage,
                'updated_by' => $user->id
            ]);
        } else {
            $commission = CommissionConfig::create([
                'type' => 'sur_mesure',
                'pourcentage' => $request->pourcentage,
                'updated_by' => $user->id
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pourcentage mis à jour',
            'pourcentage' => $commission->pourcentage
        ]);
    }
}