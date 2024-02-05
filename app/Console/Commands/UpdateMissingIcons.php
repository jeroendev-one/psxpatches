<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Game;

class UpdateMissingIcons extends Command
{
    protected $signature = 'icons:update';
    protected $description = 'Update missing icon paths in the games table';

    public function handle()
    {
        $games = Game::all();

        foreach ($games as $game) {
            $titleId = $game->title_id;
            $webpPath = "public/games/$titleId/icon0.webp";
            $pngPath = "public/games/$titleId/icon0.png";

            // Check for the existence of icon0.webp or icon0.png
            if (Storage::exists($webpPath)) {
                $game->update(['icon' => $webpPath]);
                Log::info("$titleId: Updated icon path to icon0.webp");
            } elseif (Storage::exists($pngPath)) {
                $game->update(['icon' => $pngPath]);
                Log::info("$titleId: Updated icon path to icon0.png");
            } else {
                // If neither exists, update with null
                $game->update(['icon' => null]);
                Log::info("$titleId: No icon found. Updated with null.");
            }
        }

        $this->info('Missing icons updated successfully.');
    }
}
