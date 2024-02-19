<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\API\UrlController;
use Illuminate\Support\Facades\Redis;

class ScrapWebsite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrap-website';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will scrap website every five Minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keys = Redis::keys('job:*');
        dd($keys);
        // Call the scrap method of UrlController
        $urlController = new UrlController();
        //$urlController->scrap();

        $this->info('Website scraping completed successfully!');
    }
}
