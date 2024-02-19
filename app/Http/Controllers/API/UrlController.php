<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis; // Add this import
use App\Models\Url;
use Goutte\Client;
use App\Jobs\WebScrapper;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
            // Retrieve the JSON string from Redis
            $totalHash = Redis::keys('job:*');
            $keys = Redis::keys('job:*');

            foreach ($totalHash as $key => $hash) {
                if ($hash != 'job:id') {
                    $urls[++$key] = Redis::hgetall($hash);
                }
            }

            // Check if the key exists and if it contains a value
            if (count($urls) > 0) {
                // Decode the JSON string into a PHP array
                $success = false;
                $message = 'success';
                $data = $urls;
            } else {
                // Handle case when key doesn't exist or has no value
                $success = false;
                $message  = "Key '$key' not found or contains no value.";
                $data = NULL;
            }
            // Return the extracted links as JSON response
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // If an exception occurs, there's likely an issue with the Redis connection
            $success = false;
            $messag =  "Failed to connect to Redis: " . $e->getMessage();
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate request input
            $request->validate([
                'url' => 'required',
                'status' => 'required'
            ]);

            // Store job data in Redis
            $jobId = Redis::incr('job:id');
            Redis::hmset('job:' . $jobId, [
                'id' => $jobId,
                'url' => $request->input('url'),
                //'selectors' => json_encode($request->input('selectors')),
                'status' => $request->input('status'), // Set initial status
            ]);
            // Save data to MySql Database also
            Url::store($request->all());
            // Response
            $success = true;
            $message = "Scrap URL has been successfully added";
            $data = $jobId;
            // Return the extracted links as JSON response
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // If an exception occurs, there's likely an issue with the Redis connection
            $success = false;
            $messag =  "Failed to connect to Redis: " . $e->getMessage();
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $url = Redis::hgetall('job:' . $id);
            // Response
            $success = true;
            $message = "Key '$id' data";
            $data = $url;
            // Return the extracted links as JSON response
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // If an exception occurs, there's likely an issue with the Redis connection
            $success = false;
            $messag =  "Failed to connect to Redis: " . $e->getMessage();
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {


        try {

            $request->validate([
                'url' => 'required',
                'status' => 'required'
            ]);

            // update response to Redis
            Redis::hmset('job:' . $id, [
                'id' => $id,
                'url' => $request->input('url'),
                //'scrap' => json_encode($request->all('scrap')),
                'status' => $request->input('status')
            ]);
            // Update record to MySQL Database
            Url::urlUpdate($request, $id);
            // Response
            $success = true;
            $message = "Scrap URL has been successfully added";
            $data = $id;
            // Return the extracted links as JSON response
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // If an exception occurs, there's likely an issue with the Redis connection
            $success = false;
            $messag =  "Failed to connect to Redis: " . $e->getMessage();
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Remove from Redis 
            $values = Redis::del('job:' . $id);
            // Remove from MySql database
            // Find the record you want to delete
            $record = url::find($id);
            if ($record) {
                // Delete the record
                $record->delete();
                // Response
                $success = true;
                $message = "Delete records successfully added";
                $data = $id;
            } else {
                // Response
                $success = true;
                $message = "Delete records successfully added";
                $data = $id;
            }

            // Return the extracted links as JSON response
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            // If an exception occurs, there's likely an issue with the Redis connection
            $success = false;
            $messag =  "Failed to connect to Redis: " . $e->getMessage();
            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $data,
            ]);
        }
    }

    public function scrap(string $id)
    {
        $values = Redis::hgetall('job:' . $id);
        //dd($values['url']);

        // Get the URL from the request
        $scrapUrl = $values['url'];

        // Create a new Goutte client
        $client = new Client();

        try {

            // Dispatch the job to the queue
            // Dispatch will dispache the request to job table for scrap the data
            WebScrapper::dispatch();

            // I Put this code Just for Referance 

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
            // Return the extracted links as JSON response
            return response()->json([
                'success' => true,
                'message' => "Scrap URL " . $scrapUrl,
                'data' => $id,
                'links' => $extractedLinks,
            ]);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'data' => null,
            ]);
        }
    }

    public static function scrapAllUrls() {
        
    }
}
