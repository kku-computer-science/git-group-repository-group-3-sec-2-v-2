<?php

namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class GoogleScholarProfileService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ]
        ]);
    }

    public function getProfile($userId)
    {
        $url = "https://scholar.google.com/citations?user={$userId}&hl=th&oi=ao";

        $response = $this->client->get($url);
        $html = $response->getBody()->getContents();

        return $this->parseProfile($html);
    }

    private function parseProfile($html)
    {
        $crawler = new Crawler($html);

        // ดึงชื่อโปรไฟล์
        $name = $crawler->filter('#gsc_prf_in')->count() 
            ? $crawler->filter('#gsc_prf_in')->text() 
            : 'Unknown Name';

        // ดึงสังกัด (Affiliation)
        $affiliation = $crawler->filter('.gsc_prf_il')->count() 
            ? $crawler->filter('.gsc_prf_il')->text() 
            : 'Unknown Affiliation';

        // ดึงข้อมูล Research Interests
        $interests = [];
        $crawler->filter('.gsc_prf_inta a')->each(function ($node) use (&$interests) {
            $interests[] = $node->text();
        });

        // ดึงรูปโปรไฟล์
        $image = $crawler->filter('.gsc_prf_pup-img')->count() 
            ? $crawler->filter('.gsc_prf_pup-img')->attr('src') 
            : null;

        // ดึงข้อมูลงานวิจัย (Table)
        $publications = [];
        $crawler->filter('.gsc_a_tr')->each(function ($node) use (&$publications) {
            $titleNode = $node->filter('.gsc_a_t a');
            $title = $titleNode->count() ? $titleNode->text() : 'No title';
            $link = $titleNode->count() ? 'https://scholar.google.com' . $titleNode->attr('href') : '#';

            $authors = $node->filter('.gsc_a_t div')->count() 
                ? $node->filter('.gsc_a_t div')->text() 
                : 'Unknown authors';

            $year = $node->filter('.gsc_a_y span')->count() 
                ? $node->filter('.gsc_a_y span')->text() 
                : 'No year';

            $publications[] = [
                'title' => $title,
                'link' => $link,
                'authors' => $authors,
                'year' => $year,
            ];
        });

        return [
            'name' => $name,
            'affiliation' => $affiliation,
            'interests' => $interests,
            'image' => $image ? "https://scholar.google.com$image" : null,
            'publications' => $publications,
        ];
    }
}
