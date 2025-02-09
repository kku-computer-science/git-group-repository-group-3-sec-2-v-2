<?php
require 'vendor/autoload.php';

use SerpApi\GoogleSearch;

function searchGoogleScholar($query) {
    $params = [
        "q" => $query,
        "engine" => "google_scholar",
        "api_key" => "0f5306d5274b861e6ad64a4b63d9cc7a3da034287c4fd7d18cef06d57ee36bec"
    ];

    $search = new GoogleSearch($params);
    $results = $search->getResults();

    foreach ($results['organic_results'] as $result) {
        $title = $result['title'] ?? "No Title";
        $link = $result['link'] ?? "No Link";
        $snippet = $result['snippet'] ?? "No Snippet";

        echo "Title: $title\n";
        echo "Link: $link\n";
        echo "Snippet: $snippet\n\n";
    }
}

// เรียกใช้งาน
searchGoogleScholar("machine learning");
