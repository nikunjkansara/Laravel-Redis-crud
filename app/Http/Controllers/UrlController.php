<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis; // Add this import
use App\Models\Url;
use Goutte\Client;
use app\Jobs\WebScrapper;

class UrlController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        //$value = json_encode(['id' => $uId, 'url' => 'www.example.com', 'status' => 'active']);
        //Redis::append($key, $value);
        //dd("added");
        // Retrieve existing value (assuming it's a string)
        /*$existingValue = Redis::get('urls');
        
        // Delete the existing key
        Redis::del('urls');
        
        // Push the existing value to a list along with the new value
        Redis::lpush('urls', $existingValue, $value);*/

        // Set a key
        #$value = json_encode(['id' => $uId, 'url' => 'www.example.com', 'status' => 'active']);
        #Redis::append($key, $value);
        #$result = Redis::hmget('job:1', ['url', 'response']);
        
        try {
            // Retrieve the JSON string from Redis
            $totalHash = Redis::keys('job:*');
            $keys = Redis::keys('job:*');

            // Loop through the keys
            /*foreach ($keys as $key) {
                // Retrieve the hash associated with each key
                if ($key != 'job:id') {
                    $result = Redis::hgetall($key);
                    // Add the result to the results array
                    $results[$key] = $result;
                }
            }*/

            foreach ($totalHash as $key => $hash) {
                if ($hash != 'job:id') {
                    $urls[++$key] = Redis::hgetall($hash);
                }
            }

            // Check if the key exists and if it contains a value
            if (count($urls) > 0) {
                // Decode the JSON string into a PHP array
                return view('web/home', ['urls' => $urls]);
            } else {
                // Handle case when key doesn't exist or has no value
                dd("Key '$key' not found or contains no value.");
            }
        } catch (\Exception $e) {
            // If an exception occurs, there's likely an issue with the Redis connection
            echo "Failed to connect to Redis: " . $e->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('web/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        return redirect()->route('url.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $url = Redis::hgetall('job:' . $id);
        return view('web/edit', ['url' => $url, 'key' => $id]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

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

        return redirect()->route('url.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Remove from Redis 
        $values = Redis::del('job:' . $id);
        // Remove from MySql database
        // Find the record you want to delete
        $record = url::find($id);
        if ($record) {

            // Delete the record
            $record->delete();
            // Optionally, you can return a response or redirect the user
            return redirect()->route('url.index')->with('success', 'Record deleted successfully');
        } else {
            // Handle the case where the record is not found
            return redirect()->route('url.index')->with('error', 'Record not found');
        }

        return redirect()->route('url.index');
    }

    public function scrap(string $id)
    {
        $values = Redis::hgetall('job:' . $id);
        //dd($values['urls']);

        // Get the URL from the request
        $scrapUrl = "http://www.technologiespost.com";

        // Create a new Goutte client
        $client = new Client();

        try {

            // Dispatch the job to the queue
            // Dispatch will dispache the request to job table for scrap the data
            WebScrapper::dispatch($values)->onQueue('high_priority');

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
                'links' => $extractedLinks,
            ]);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
}
