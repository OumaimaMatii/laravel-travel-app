<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Reservation;
use App\Traits\ImageUploadTrait;
use App\Traits\Notifiable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    use ImageUploadTrait, Notifiable;

    // POST /documents/upload/{reservation} - Upload d'un document (agent/admin uniquement)
    public function upload(Request $request, $reservationId)
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['agent', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé. Seuls les agents peuvent uploader des documents.'
            ], 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpeg,png,jpg|max:5120',
            'titre' => 'required|string|max:255',
            'type' => 'required|in:billet_avion,billet_train,confirmation_hotel,itineraire,assurance,facture,visa,autre',
        ]);

        $reservation = Reservation::findOrFail($reservationId);
        
        // Stockage du fichier dans storage/app/public/documents
        $file = $request->file('document');
        $path = $file->store('documents/reservation_' . $reservationId, 'public');
        
        // Création de l'enregistrement en base de données
        $document = Document::create([
            'reservation_id' => $reservationId,
            'uploaded_by' => $user->id,
            'titre' => $request->titre,
            'type' => $request->type,
            'chemin_fichier' => $path,
            'nom_fichier_original' => $file->getClientOriginalName(),
            'taille' => round($file->getSize() / 1024, 2),
        ]);

        // Notification au client
        $this->sendNotification(
            $reservation->user_id,
            'Nouveau document disponible',
            "Le document '{$request->titre}' a été ajouté à votre réservation #{$reservationId}",
            'document',
            "/reservations/{$reservationId}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Document ajouté avec succès',
            'data' => $document
        ], 201);
    }

    // GET /documents/{id}/download - Télécharger un document (client propriétaire ou agent/admin)
    public function download(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $document = Document::findOrFail($id);
        $reservation = $document->reservation;

        // Vérification des droits d'accès
        if ($user->role === 'client' && $reservation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $path = storage_path('app/public/' . $document->chemin_fichier);
        
        if (!file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'Fichier non trouvé'
            ], 404);
        }

        return response()->download($path, $document->nom_fichier_original);
    }

    // GET /reservations/{reservation}/documents - Liste les documents d'une réservation
    public function index(Request $request, $reservationId)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Non authentifié'
            ], 401);
        }
        
        $reservation = Reservation::findOrFail($reservationId);
        
        if ($user->role === 'client' && $reservation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $documents = Document::where('reservation_id', $reservationId)->get();
        
        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    // DELETE /documents/{id} - Supprime un document (agent/admin uniquement)
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        
        if (!$user || !in_array($user->role, ['agent', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $document = Document::findOrFail($id);
        
        // Suppression du fichier physique
        if (Storage::disk('public')->exists($document->chemin_fichier)) {
            Storage::disk('public')->delete($document->chemin_fichier);
        }
        
        $document->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Document supprimé avec succès'
        ], 200);
    }
}