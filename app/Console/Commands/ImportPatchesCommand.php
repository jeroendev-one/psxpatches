<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use App\Models\Patch;
use Illuminate\Support\Facades\File;

class ImportPatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:patches {path : Path to JSON files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import patches from JSON files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = $this->argument('path');

        // Validate required command line arguments
        if (empty($path)) {
            $this->error('The "path" argument is required.');
            return;
        }

        $files = File::glob("$path/*.json");

        foreach ($files as $file) {
            // Extract title_id and version from the filename
            $filename = pathinfo($file, PATHINFO_FILENAME);
            [$titleId, $version] = explode('_', $filename);

            $json = json_decode(file_get_contents($file), true);

            if (!$json || !isset($json['success']) || !$json['success']) {
                $this->warn("Invalid JSON format or 'success' is false in file: $file. Skipping.");
                continue;
            }

            // Find or create the game based on title_id
            $game = Game::firstOrCreate(['title_id' => $titleId]);

            // Check if the patch with the same version already exists
            $patch = $game->patches()->updateOrCreate(
                ['version' => $version],
                [
                    'icon' => $json['icon'] ?? null,
                    'size' => $json['size'],
                    'endpoint' => $json['endpoint'],
                ]
            );

            if ($patch->wasChanged()) {
                $this->info("Patch updated for $titleId patch version: $version");
            } //else {
                //$this->info("No changes for $titleId patch with version: $version");
            //}
        }

        $this->info('Import completed.');
    }
}
