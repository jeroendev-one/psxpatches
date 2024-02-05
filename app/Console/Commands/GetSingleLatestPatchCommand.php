<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use App\Models\Game;
use App\Models\Path;

class GetSingleLatestPatchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:singlepatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $games = Game::where('title_id', 'CUSA46853')->get();
    
        foreach ($games as $game) {
            // Make a request to the update XML URL
            $response = Http::withoutVerifying()->get($this->getUpdateXmlUrl($game->title_id));
    
            // Check if the request was successful
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                $xmlVersion = (string)$xml->tag->package['version'];
                $xmlContentId = (string)$xml->tag->package['content_id'];
                $xmlManifestUrl = (string)$xml->tag->package['manifest_url'];
                $xmlSize = (string)$xml->tag->package['size'];
    
                // Check if $game->content_id is not set
                if (!$game->content_id) {
                    // Update $game->content_id if not already set
                    $game->content_id = $xmlContentId;
                    $game->save();
                    $this->info('Content ID updated.');
                }
    
                // Check if there are patches for the game
                if ($game->patches->isEmpty()) {
                    // Create a new patch for the game instance
                    $newPatch = $game->patches()->create([
                        'version' => $xmlVersion,
                        'size' => $xmlSize,
                        'endpoint' => $xmlManifestUrl,
                    ]);
    
                    // Print new version
                    $this->info($game->title_id . ': New patch added: ' . $xmlVersion . '!' . PHP_EOL);
                } else {
                    // Get the latest patch by sorting patches by version in descending order
                    $latestPatch = $game->patches->sortByDesc('version')->first();
    
                    // Compare the versions
                    if (version_compare($xmlVersion, $latestPatch->version, '>')) {
                        // Update the game with new version and patch size
                        $game->update([
                            'current_version' => $xmlVersion,
                            'latest_patch_size' => $xmlSize,
                        ]);
    
                        // Create a new patch for the game instance
                        $newPatch = $game->patches()->create([
                            'version' => $xmlVersion,
                            'size' => $xmlSize,
                            'endpoint' => $xmlManifestUrl,
                        ]);
    
                        // Print new version
                        $this->info($game->title_id . ': New patch added: ' . $xmlVersion . '!' . PHP_EOL);
                    } else {
                        $this->info($game->title_id . ': No newer version available.' . PHP_EOL);
                    }
                }
            } else {
                $this->error('Failed to fetch update XML for ' . $game->title_id);
            }
        }
    }
    
    // Helper function to get the update XML URL
    private function getUpdateXmlUrl($titleId)
    {
        $byteKey = hex2bin('AD62E37F905E06BC19593142281C112CEC0E7EC3E97EFDCAEFCDBAAFA6378D84');
        $gameIdBytes = utf8_encode("np_$titleId");
        $hash = hash_hmac('sha256', $gameIdBytes, $byteKey);
    
        return "https://gs-sec.ww.np.dl.playstation.net/plo/np/$titleId/$hash/$titleId-ver.xml";
    }
}    
