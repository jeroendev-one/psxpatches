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

use Exception;

class ProcessPatchesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $start;
    public $end;

    public $timeout = 900;

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
        $chunkSize = 20; // Adjust as needed
        $totalEntries = Game::where('title_id', 'like', 'CUSA%')->count();
        
        while (true) {
            for ($start = 0; $start < $totalEntries; $start += $chunkSize) {
                $end = min($start + $chunkSize - 1, $totalEntries - 1);
    
                try {
                    Log::channel('patches')->info("Processing range: {$start}-{$end}");
                    
                    $games = Game::where('title_id', 'like', 'CUSA%')
                        ->skip($start)
                        ->take($end - $start + 1)
                        ->get();
    
                    foreach ($games as $game) {
                        try {
                            sleep(1);
                            // Make a request to the update XML URL
                            $response = Http::withoutVerifying()->timeout(60)->get($this->getUpdateXmlUrl($game->title_id));
                    
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
                                    Log::channel('patches')->info($game->title_id . ': Content ID updated.');
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
                                    Log::channel('patches')->info($game->title_id . ': New patch added: ' . $xmlVersion . '!' . PHP_EOL);
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
                                        Log::channel('patches')->info($game->title_id . ': New patch added: ' . $xmlVersion . '!' . PHP_EOL);
                                    } else {
                                        Log::channel('patches')->info($game->title_id . ': No new version.' . PHP_EOL);
                                    }
                                }
                            } else {
                                Log::channel('patches')->info($game->title_id . ': Failed to fetch update XML.');
                            }
                        } catch (Exception $e) {
                            Log::channel('patches')->error("Error processing game {$game->title_id}: " . $e->getMessage());
                        }
                    }
                } catch (Exception $e) {
                    Log::channel('patches')->error("Error processing range: {$start}-{$end}: " . $e->getMessage());
                }
            }
            Log::channel('patches')->info("Finished processing all ranges. Starting over.");
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