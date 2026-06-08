<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Media;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Activite;

class MediaSeeder extends Seeder
{
    public function run(): void
    {
        $destinations = Destination::all();
        $hotels = Hotel::all();
        $activites = Activite::all();
        
        // Medias pour les destinations
        foreach ($destinations as $index => $destination) {
            Media::updateOrCreate(
                [
                    'mediable_type' => Destination::class,
                    'mediable_id' => $destination->id,
                    'ordre' => 0,
                ],
                [
                    'url' => 'destinations/' . strtolower(str_replace(' ', '_', $destination->nom)) . '_main.jpg',
                    'titre' => $destination->nom . ' - Vue principale',
                    'ordre' => 0,
                    'est_principale' => true,
                ]
            );
            
            Media::updateOrCreate(
                [
                    'mediable_type' => Destination::class,
                    'mediable_id' => $destination->id,
                    'ordre' => 1,
                ],
                [
                    'url' => 'destinations/' . strtolower(str_replace(' ', '_', $destination->nom)) . '_gallery.jpg',
                    'titre' => $destination->nom . ' - Galerie',
                    'ordre' => 1,
                    'est_principale' => false,
                ]
            );
        }
        
        // Medias pour les hotels
        foreach ($hotels as $hotel) {
            Media::updateOrCreate(
                [
                    'mediable_type' => Hotel::class,
                    'mediable_id' => $hotel->id,
                ],
                [
                    'url' => 'hotels/' . strtolower(str_replace(' ', '_', $hotel->nom)) . '.jpg',
                    'titre' => $hotel->nom,
                    'ordre' => 0,
                    'est_principale' => true,
                ]
            );
        }
        
        // Medias pour les activites
        foreach ($activites as $activite) {
            Media::updateOrCreate(
                [
                    'mediable_type' => Activite::class,
                    'mediable_id' => $activite->id,
                ],
                [
                    'url' => 'activites/' . strtolower(str_replace(' ', '_', $activite->nom)) . '.jpg',
                    'titre' => $activite->nom,
                    'ordre' => 0,
                    'est_principale' => true,
                ]
            );
        }
        
        $this->command->info('Medias crees avec succes');
    }
}