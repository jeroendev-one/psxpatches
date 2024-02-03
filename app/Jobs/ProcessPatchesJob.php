<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use App\Models\Game;
use App\Models\Patch;

class ProcessPatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $start;
    public $end;

    public $timeout = 600;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::channel('patches')->info("Processing range: {$this->start}-{$this->end}");
        
        $games = Game::where('title_id', 'like', 'CUSA%')
            ->skip($this->start)
            ->take($this->end - $this->start + 1)
            ->get();

        foreach ($games as $game) {
            sleep(1);
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
                $response = Http::withoutVerifying()->timeout(60)->get($updateXmlUrl);
        
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

                        // Update $game->content_id if not already set
                        $game->content_id = $xmlContentId;
                        $game->save();
                        Log::channel('patches')->info($titleId . ': Content ID updated.');
                    }

                    // Compare the versions
                    if (version_compare($xmlVersion, $latestVersion, '>')) {
                        
                        // Create a new patch for the game instance
                        $newPatch = $game->patches()->create([
                            'version' => $xmlVersion,
                            'size' => $xmlSize,
                            'endpoint' => $xmlManifestUrl,
                        ]);

                        // Update the game with new version and patch size
                        $game->update([
                            'current_version' => $xmlVersion,
                            'latest_patch_size' => $xmlSize,
                        ]);

                        // Print new version
                        Log::channel('patches')->info($titleId . ': New patch added: ' . $xmlVersion . '!' . PHP_EOL);

                    } else {
                        Log::channel('patches')->info($titleId . ': No newer version available.'. PHP_EOL);
                    }
                } else {
                    Log::Log::channel('patches')->info($titleId. ': Failed to fetch update XML.');
                }
            }
        }
        Log::channel('patches')->info("Finished processing range: {$this->start}-{$this->end}");
    }
}
