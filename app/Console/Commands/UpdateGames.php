<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessUpdateGamesJob;

class UpdateGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:games';

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
            
        $chunkSize = 10000;
        $totalEntries = 80000;
        $totalChunks = ceil($totalEntries / $chunkSize);

        for ($chunkNumber = 0; $chunkNumber < $totalChunks; $chunkNumber++) {
            $start = $chunkNumber * $chunkSize;
            $end = min($start + $chunkSize - 1, $totalEntries - 1);
        
            // Dispatch a job for each range with start and end values
            ProcessUpdateGamesJob::dispatch($start, $end)->onQueue('high');
        }
        
        $this->info('Jobs dispatched successfully.');
    }
}
