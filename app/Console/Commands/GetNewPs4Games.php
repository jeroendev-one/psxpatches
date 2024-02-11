<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessNewGamesJob;
use Illuminate\Support\Facades\Log;

use App\Models\Game;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

use App\Models\Patch;

class GetNewPs4Games extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:newps4games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        
        $startRange = 46500;
        $endRange = 70000; // Adjust this according to your actual end range
        $chunkSize = 500;
        
        for ($start = $startRange; $start <= $endRange; $start += $chunkSize) {
            $end = min($start + $chunkSize - 1, $endRange);
            $range = "$start-$end";
            $this->line("Processing range: $range");
            
            // Dispatch a job for each range with start and end values
            ProcessNewGamesJob::dispatch($start, $end)->onQueue('new_games');
        }
        $this->info('Jobs dispatched successfully.');
        
/*
        //dd($game);
        $start = 46500;
        $end = 70000;
        $range = "$start-$end";
        $this->line("Processing range: $range");
        ProcessNewGamesJob::dispatch($start, $end)->onQueue('high');
        //$this->info('Jobs dispatched successfully.');
        */
/*
        try {
            // Loop over title ids
            for ($i = $start; $i <= $end; $i++) {
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
                    else {
                        Log::channel('games')->error("Failed to fetch update XML for title: $titleId");
                    }
                } else {
                    Log::channel('games')->info("Skipping title: $titleId (already exists)");
                }
            }
        } catch (Exception $e) {
            // Log the exception
            Log::channel('games')->error('Exception occurred in ProcessNewGamesJob: ' . $e->getMessage());
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
    */
}
}        
