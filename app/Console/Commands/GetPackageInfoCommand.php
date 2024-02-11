<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Game;
use GuzzleHttp\Client;

class GetPackageInfoCommand extends Command
{
    protected $signature = 'get:packageinfo {--title_id= : Optional title_id for update}';
    protected $description = 'Retrieve concept ID for a PlayStation product.';

    public function handle()
    {
        $titleId = $this->option('title_id');
    
        if ($titleId) {
            // If a title_id is provided, retrieve only that specific game
            $game = Game::where('title_id', $titleId)->whereNotNull('content_id')->first();
    
            if ($game) {
                // Process the single game
                $this->processGame($game);
            } else {
                $this->error("No game found with title_id: $titleId");
            }
        } else {
            // If no title_id is provided, retrieve all games
            $games = Game::whereNotNull('content_id')->get();
    
            foreach ($games as $game) {
                // Process each game in the loop
                $this->processGame($game);
            }
        }
    }

    private function getIcon(array $jsonResponse) {
        foreach ($jsonResponse['data']['productRetrieve']['concept']['media'] as $media) {
            if (isset($media['role']) && $media['role'] == 'MASTER') {
                $icon = $media['url'];

                if(isset($icon)) {
                    return $icon;
                }
            }
        }
    }

    private function getPublisher(array $jsonResponse) {
        $publisher = $jsonResponse['data']['productRetrieve']['concept']['publisherName'];
        if($publisher) {
            return $publisher;
        }
    }

    private function processGame($game) {

            //$this->info('Processing: ' . $game->title_id);
            //$this->info($game->content_id);
            
            $region = substr($game->content_id, 0, 2);

            switch ($region) {
                case 'JP':
                    $region = 'ja-jp';
                    break;
                case 'EP':
                    $region = 'en-gb';
                    break;
                case 'UP':
                    $region = 'en-us';
                    break;
                default:

            }
            //$this->info($region);
            $url = 'https://web.np.playstation.com/api/graphql/v1/op';
            $headers = ['x-psn-store-locale-override' => $region];
            $payload = [
                'operationName' => 'metGetConceptByProductIdQuery',
                'variables' => json_encode(["productId" => $game->content_id]),
                'extensions' => '{"persistedQuery":{"version":1,"sha256Hash":"0a4c9f3693b3604df1c8341fdc3e481f42eeecf961a996baaa65e65a657a6433"}}'
            ];

            $client = new Client();
            $response = $client->get($url, ['headers' => $headers, 'query' => $payload]);
            
            if ($response->getStatusCode() !== 200) {
                $this->error('Request failed');
                $this->error('Status code: ' . $response->getStatusCode());
                return;
            }
            
            $jsonResponse = json_decode($response->getBody(), true);

            if (isset($jsonResponse['errors'])) {
                $this->info($game->title_id . ' Encountered JSON error.' . PHP_EOL);
                //dd($jsonResponse);
                return;
            }


            if (!$game->icon) {
                $iconUrl = $this->getIcon($jsonResponse);
            
                if ($iconUrl) {
                    $this->info($iconUrl);
                    $this->info($game->icon);
                    $extension = pathinfo($iconUrl, PATHINFO_EXTENSION);
                    $localPath = "games/{$game->title_id}/icon0.{$extension}";
            
                    // Download the image and save it locally
                    $contents = file_get_contents($iconUrl);
                    \Storage::disk('public')->put($localPath, $contents);
            
                    // Update the database with the local path
                    $game->update(['icon' => $localPath]);
                    $this->info($game->title_id . ': Icon updated.'. PHP_EOL);
                }
            }

            if(!$game->publisher or ($game->publisher == 'Unknown')){
                $publisher = $this->getPublisher($jsonResponse);
            
                if(isset($publisher)) {
                    $game->update([
                        'publisher' => $publisher,
                    ]);
                    $this->info($game->title_id . ': Publisher updated.'. PHP_EOL);
                }

            }

            $this->info('Processed: ' . $game->title_id);
        }
    }
