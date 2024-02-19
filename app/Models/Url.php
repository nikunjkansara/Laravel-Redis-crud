<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Goutte\Client;
use Illuminate\Support\Facades\Log;

class Url extends Model
{
    use HasFactory;
    protected $fillable = ['url', 'status', 'scrap'];


    public static function store($request)
    {
        $id = url::create($request);
        return $id;
    }

    public static function urlUpdate($request, $id)
    {
        // Save data to MySql Database also
        $record = Url::findOrFail($id);
        $record->url = $request->input('url');
        $record->status = $request->input('status');
        $record->save();
    }

    public static function webScrapping()
    {
        Log::info('Scraping started: ');
        // Retrieve the JSON string from Redis
        $totalHash = Redis::keys('job:*');
        $keys = Redis::keys('job:*');

        foreach ($totalHash as $key => $hash) {
            if ($hash != 'job:id') {
                $urls = Redis::hgetall($hash);
                $id = $urls['id'];
                Log::debug('Starting web scraping task '.$key.' for:'. $id);

                $values = Redis::hgetall('job:' . $id);
                //dd($values['url']);

                // Get the URL from the request
                $scrapUrl = $values['url'];
                Log::debug('Starting web scraping task'.$key.' for URL:'. $scrapUrl);
                // Create a new Goutte client
                $client = new Client();

                try {
                    // Fetch the webpage
                    $crawler = $client->request('GET', $scrapUrl);

                    // Extract all links from the webpage
                    $links = $crawler->filter('a')->links();

                    // Extract and output the href attribute of each link
                    $extractedLinks = [];
                    foreach ($links as $link) {
                        $extractedLinks[] = $link->getUri();
                    }

                    // update response to Redis
                    Redis::hmset('job:' . $id, [
                        'scrap' => json_encode($extractedLinks)
                    ]);
                    Log::debug('Updated webscrap data to '.$key.' for URL:'. $scrapUrl);
                   
                } catch (\Exception $e) {
                    // Handle any errors
                    $response =  response()->json([
                        'success' => false,
                        'message' => 'Error: ' . $e->getMessage(),
                        'data' => null,
                    ]);
                    Log::debug('Error Webscrapping with Response:  '.$response);
                }
            }
        }
        Log::info('End Scraping started: ');
    }
}
