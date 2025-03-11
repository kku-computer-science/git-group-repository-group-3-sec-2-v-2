<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ScholarService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = config('services.serpapi.key');
    }

    public function searchScholar($name)
    {
        $url = "https://serpapi.com/search.json";
        $params = [
            'engine' => 'google_scholar_profiles',
            'q' => $name,
            'api_key' => $this->apiKey
        ];

        try {
            $response = $this->client->get($url, ['query' => $params]);
            $data = json_decode($response->getBody(), true);

            return $data['profiles'] ?? [];
        } catch (\Exception $e) {
            Log::error("Error fetching scholar data: " . $e->getMessage());
            return null;
        }
    }

    public function getScholarInfo($scholarId)
    {
        $url = "https://serpapi.com/search.json";
        $params = [
            'engine' => 'google_scholar_author',
            'author_id' => $scholarId,
            'api_key' => $this->apiKey
        ];

        try {
            $response = $this->client->get($url, ['query' => $params]);
            $data = json_decode($response->getBody(), true);

            return $data ?? null;
        } catch (\Exception $e) {
            Log::error("Error fetching scholar info: " . $e->getMessage());
            return null;
        }
    }
}
