<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImportGamesCommand extends Command
{
    protected $signature = 'import:games {path : Path to JSON files} {--title_id= : Optional title_id for update or create}';

    protected $description = 'Import games from JSON files';

    public function handle()
    {
        $path = $this->argument('path');
        $titleId = $this->option('title_id');

        // Validate required command line arguments
        if (empty($path)) {
            $this->error('The "path" argument is required.');
            return;
        }

        if (empty($titleId)) {
            $this->error('The "--title_id" option is required.');
            return;
        }
        

        $files = File::glob($path . '/*.json');

        foreach ($files as $file) {
            $json = json_decode(file_get_contents($file), true);

            if (!$json || !isset($json['metadata'])) {
                $this->warn("Invalid JSON format or missing 'metadata' in file: $file. Skipping.");
                continue;
            }

            $metadata = $json['metadata'];

            $game = Game::updateOrCreate(
                [
                    'name' => $metadata['name'],
                    'title_id' => $titleId,
                    'current_version' => $metadata['currentVersion'],
                    'region' => $metadata['region'],
                    'latest_patch_size' => $metadata['latestPatch']['size'] ?? null,
                ]
            );

            // Save images to public storage
            if (isset($metadata['icon'])) {
                $iconPath = "games/$titleId/icon0.webp"; // Modified path
                //Storage::put($iconPath, file_get_contents($metadata['icon'])); // Use Storage facade
                $game->update(['icon' => $iconPath]); // Update the database
            }

            // Save images to public storage
            if (isset($metadata['background'])) {
                $backgroundPath = "games/$titleId/pic0.webp"; // Modified path
                //Storage::put($backgroundPath, file_get_contents($metadata['background'])); // Use Storage facade
                $game->update(['background' => $backgroundPath]); // Update the database
            }

            $this->info("Game imported: {$metadata['name']}");
        }

        $this->info('Import completed.');
    }
}
