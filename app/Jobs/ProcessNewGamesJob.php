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

class ProcessNewGamesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
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
    Log::channel('games')->info("Processing range: {$this->start}-{$this->end}");

    // Loop over title ids
    for ($i = $this->start; $i <= $this->end; $i++) {
        $titleId = "CUSA$i";
        
        // Check if the game with the same title_id exists in the Game model
        $existingGame = Game::where('title_id', $titleId)->exists();

        // If the game with the same title_id does not exist, proceed with processing
        if (!$existingGame) {
            Log::channel('games')->info("Processing title: $titleId");

            sleep(1);
            // Make a request to the update XML URL
            $response = Http::withoutVerifying()->timeout(60)->get($this->getUpdateXmlUrl($titleId));

            // Check if the request was successful
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body());
                $title = (string)$xml->tag->package->paramsfo->title;
                $xmlVersion = (string)$xml->tag->package['version'];
                $xmlContentId = (string)$xml->tag->package['content_id'];
                $xmlSize = (string)$xml->tag->package['size'];
                $xmlManifestUrl = (string)$xml->tag->package['manifest_url']; // Extract manifest URL from XML
                
                // Create new game
                $newGame = Game::create([
                    'name' => $title,
                    'title_id' => $titleId,
                    'current_version' => $xmlVersion,
                    'content_id' => $xmlContentId,
                    'latest_patch_size' => $xmlSize,
                    'region' => 'Unknown',
                ]);

                Log::channel('games')->info("New game created: $titleId - $title");

                // Create a new patch associated with the newly created game entry
                $newPatch = $newGame->patches()->create([
                    'version' => $xmlVersion,
                    'size' => $xmlSize,
                    'endpoint' => $xmlManifestUrl,
                ]);

                Log::channel('games')->info("New patch created for game: $titleId - Version: $xmlVersion");
            }
        } else {
            Log::channel('games')->info("Skipping title: $titleId (already exists)");
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
