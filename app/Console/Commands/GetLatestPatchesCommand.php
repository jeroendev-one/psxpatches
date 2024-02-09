<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use App\Jobs\ProcessPatchesJob;

use App\Models\Game;
use App\Models\Patch;

class GetLatestPatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:patches  {--batch= : Optional title_id for manual run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get latest versions of games';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Dispatch the job to process patches
        ProcessPatchesJob::dispatch()->onQueue('high');
        
        $this->info('Patch processing job dispatched successfully.');
    }
}
/*
    public function handle()
    {
        $games = Game::all();
        foreach ($games as $game) {
            // Get the latest patch by sorting patches by version in descending order
            $latestPatch = $game->patches->sortByDesc('version')->first();
        
            if ($latestPatch) {
                // Access the version of the latest patch
                $latestVersion = $latestPatch->version;
                $titleId = $game->title_id;
                $byteKey = hex2bin('AD62E37F905E06BC19593142281C112CEC0E7EC3E97EFDCAEFCDBAAFA6378D84');
                $gameIdBytes = utf8_encode("np_$titleId");
                $hash = hash_hmac('sha256', $gameIdBytes, $byteKey);
        
                $updateXmlUrl = "https://gs-sec.ww.np.dl.playstation.net/plo/np/$titleId/$hash/$titleId-ver.xml";

        
                //$this->info('Title ID: ' . $titleId);
                //$this->info('Latest Patch Version: ' . $latestVersion);
                //$this->info('Update XML: ' . $updateXmlUrl);
                // Make a request to the update XML URL
                $response = Http::withoutVerifying()->get($updateXmlUrl);
        
                // Check if the request was successful
                if ($response->successful()) {
                    $xml = simplexml_load_string($response->body());
                    
                    // Extract information from the XML
                    $xmlVersion = (string)$xml->tag->package['version'];
                    $xmlContentId = (string)$xml->tag->package['content_id'];

                    $xmlManifestUrl = (string)$xml->tag->package['manifest_url'];
                    $xmlSize = (string)$xml->tag->package['size'];

                    // Check if $game->content_id is not set
                    if (!$game->content_id) {
                        dd($game->content_id);
                        // Update $game->content_id if not already set
                        $game->content_id = $xmlContentId;
                        $game->save();
                        $this->info('Content ID updated.');
                    }

                    // Compare the versions
                    if (version_compare($xmlVersion, $latestVersion, '>')) {
                        
                        // Create a new patch for the game instance
                        $newPatch = $game->patches()->create([
                            'version' => $xmlVersion,
                            'size' => $xmlSize,
                            'endpoint' => $xmlManifestUrl,
                        ]);

                        // Print new version
                        $this->info($titleId . ': New patch added: ' . $xmlVersion . '!' . PHP_EOL);

                    } else {
                        $this->info($titleId . ': No newer version available.'. PHP_EOL);
                    }
                } else {
                    $this->error('Failed to fetch update XML.');
                }
            }
        }
    }
}
*/