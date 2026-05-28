<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActiviteController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\DetailReservationController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\HotelTypeChambreController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\StatutForfaitController;
use App\Http\Controllers\Api\StatutSurMesureController; 
use App\Http\Controllers\Api\TransportController;
use App\Http\Controllers\Api\TypeChambreController;
use App\Http\Controllers\Api\TypeForfaitController;
use App\Http\Controllers\Api\TypeTransportController;
use App\Http\Controllers\Api\TypeVoyageController;
use App\Http\Controllers\Api\VilleController;
use App\Http\Controllers\Api\VoyageActiviteController;
use App\Http\Controllers\Api\VoyageController;
use App\Http\Controllers\Api\VoyageForfaitController;
use App\Http\Controllers\Api\VoyageSurMesureController;
use App\Http\Controllers\Api\VoyageTransportController;
use App\Http\Controllers\Api\VoyageurController;
use App\Http\Controllers\Api\SurMesureController;

// Routes PUBLIQUES (non authentifiées)


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Catalogue public
Route::get('/forfaits',         [VoyageForfaitController::class, 'index']);
Route::get('/forfaits/{id}',    [VoyageForfaitController::class, 'show']);
Route::get('/destinations',     [DestinationController::class, 'index']);
Route::get('/destinations/{id}',[DestinationController::class, 'show']);
Route::get('/activites',        [ActiviteController::class, 'index']);
Route::get('/activites/{id}',   [ActiviteController::class, 'show']);
Route::get('/hotels',           [HotelController::class, 'index']);
Route::get('/hotels/{id}',      [HotelController::class, 'show']);
Route::get('/villes',           [VilleController::class, 'index']);
Route::get('/type-chambres',    [TypeChambreController::class, 'index']);
Route::get('/type-transports',  [TypeTransportController::class, 'index']);
Route::get('/type-forfaits',    [TypeForfaitController::class, 'index']);
Route::get('/statut-forfait',   [StatutForfaitController::class, 'index']);
Route::get('/commission',       [CommissionController::class, 'getPourcentage']);
Route::get('/transports/publics', [TransportController::class, 'getPublicTransports']);

// Routes AUTHENTIFIÉES

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user',    [AuthController::class, 'user']);

    Route::get('/documents/{id}/download', [DocumentController::class, 'download']);

    //CLIENT
    Route::middleware('role:client')->prefix('client')->group(function () {

        Route::get('/notifications',              [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{id}/read',  [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all',    [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}',      [NotificationController::class, 'destroy']);

        Route::get('/reservations',                       [ReservationController::class, 'mesReservations']);
        Route::get('/reservations/{id}',                  [ReservationController::class, 'show']);
        Route::post('/reservations/{id}/annuler',         [ReservationController::class, 'annuler']);
        Route::post('/reservations/{id}/confirmer',       [ReservationController::class, 'confirmer']);
        Route::get('/reservations/{id}/documents',        [DocumentController::class, 'index']);
        Route::get('/reservations/{id}/details-chambres', [DetailReservationController::class, 'byReservation']);
        Route::post('/reservations/forfait', [ReservationController::class, 'storeForfait']);

        Route::get('/sur-mesure/transports', [SurMesureController::class, 'getTransportsPublics']);
        Route::get('/sur-mesure/commission', [SurMesureController::class, 'getCommission']);
        Route::post('/sur-mesure/calculer',  [SurMesureController::class, 'calculerPrix']);
        Route::get('/sur-mesure',      [VoyageSurMesureController::class, 'mesDemandes']);
        Route::post('/sur-mesure',     [SurMesureController::class, 'store']);
        Route::get('/sur-mesure/{id}', [VoyageSurMesureController::class, 'show']);
    });

    //AGENT
    Route::middleware('role:agent')->prefix('agent')->group(function () {

        Route::get('/notifications',              [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{id}/read',  [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all',    [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}',      [NotificationController::class, 'destroy']);

        Route::get('/forfaits',         [VoyageForfaitController::class, 'mesForfaits']);
        Route::get('/forfaits/{id}',    [VoyageForfaitController::class, 'show']);
        Route::post('/forfaits',        [VoyageForfaitController::class, 'store']);
        Route::put('/forfaits/{id}',    [VoyageForfaitController::class, 'update']);
        Route::delete('/forfaits/{id}', [VoyageForfaitController::class, 'destroy']);

        Route::get('/forfaits/{forfaitId}/reservations', [ReservationController::class, 'getReservationsByForfait']);
        Route::get('/reservations/{id}',                 [ReservationController::class, 'showAgent']);

        Route::post('/reservations/{reservationId}/documents', [DocumentController::class, 'upload']);
        Route::get('/reservations/{reservationId}/documents',  [DocumentController::class, 'index']);
        Route::delete('/documents/{id}',                       [DocumentController::class, 'destroy']);

        Route::get('/sur-mesure',      [VoyageSurMesureController::class, 'index']);
        Route::get('/sur-mesure/{id}', [VoyageSurMesureController::class, 'show']);
        Route::put('/sur-mesure/{id}', [VoyageSurMesureController::class, 'update']);

        Route::get('/type-transports/forfait', [TypeTransportController::class, 'getForfaitTypes']);
        Route::post('/reservations/{id}/confirmer-annulation-transport',
            [ReservationController::class, 'confirmerAnnulationTransport']);
    });

    //ADMIN (accès total)
   
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        Route::get('/reservations',         [ReservationController::class, 'index']);
        Route::get('/reservations/{id}',    [ReservationController::class, 'showAgent']);
        Route::put('/reservations/{id}',    [ReservationController::class, 'update']);
        Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
        Route::get('/forfaits/{forfaitId}/reservations',
            [ReservationController::class, 'getReservationsByForfait']);
        Route::post('/reservations/{id}/confirmer-annulation-transport',
            [ReservationController::class, 'confirmerAnnulationTransport']);

        Route::get('/forfaits',         [VoyageForfaitController::class, 'index']);
        Route::get('/forfaits/{id}',    [VoyageForfaitController::class, 'show']);
        Route::post('/forfaits',        [VoyageForfaitController::class, 'store']);
        Route::put('/forfaits/{id}',    [VoyageForfaitController::class, 'update']);
        Route::delete('/forfaits/{id}', [VoyageForfaitController::class, 'destroy']);

        Route::get('/sur-mesure',         [VoyageSurMesureController::class, 'index']);
        Route::get('/sur-mesure/{id}',    [VoyageSurMesureController::class, 'show']);
        Route::put('/sur-mesure/{id}',    [VoyageSurMesureController::class, 'update']);
        Route::delete('/sur-mesure/{id}', [VoyageSurMesureController::class, 'destroy']);

        Route::apiResource('/destinations',    DestinationController::class)->except(['index','show']);
        Route::post('/destinations/{id}',      [DestinationController::class, 'update']);
        Route::apiResource('/hotels',          HotelController::class)->except(['index','show']);
        Route::post('/hotels/{id}',            [HotelController::class, 'update']);
        Route::apiResource('/activites',       ActiviteController::class)->except(['index','show']);
        Route::post('/activites/{id}',         [ActiviteController::class, 'update']);
        Route::apiResource('/villes',          VilleController::class)->except(['index']);
        Route::apiResource('/type-chambres',   TypeChambreController::class)->except(['index']);
        Route::apiResource('/type-transports', TypeTransportController::class)->except(['index']);
        Route::apiResource('/type-forfaits',   TypeForfaitController::class)->except(['index']);
        Route::apiResource('/statut-forfait',  StatutForfaitController::class)->except(['index']);
        Route::apiResource('/statut-sur-mesure', StatutSurMesureController::class);
        Route::apiResource('/type-voyages',    TypeVoyageController::class);
        Route::apiResource('/transports',      TransportController::class);
        Route::apiResource('/hotel-type-chambre', HotelTypeChambreController::class);
        Route::apiResource('/medias',          MediaController::class);
        Route::post('/medias/multiple',        [MediaController::class, 'storeMultiple']);

        Route::put('/commission', [CommissionController::class, 'updatePourcentage']);

        Route::post('/reservations/{id}/documents', [DocumentController::class, 'upload']);
        Route::delete('/documents/{id}',            [DocumentController::class, 'destroy']);

        Route::get('/notifications',              [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::patch('/notifications/{id}/read',  [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all',    [NotificationController::class, 'markAllAsRead']);
        Route::delete('/notifications/{id}',      [NotificationController::class, 'destroy']);

        Route::apiResource('/voyageurs',          VoyageurController::class);
        Route::apiResource('/detail-reservations',DetailReservationController::class);
        Route::apiResource('/voyage-activites',   VoyageActiviteController::class);
        Route::apiResource('/voyage-transports',  VoyageTransportController::class);
        Route::get('/voyages',    [VoyageController::class, 'index']);
        Route::get('/voyages/{id}',[VoyageController::class,'show']);
    });
});