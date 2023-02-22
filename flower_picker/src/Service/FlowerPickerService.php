<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\DomCrawler\Crawler;

class FlowerPickerService
{
    public function __construct(private readonly HttpClientInterface $client)
    {
        //private readonly $client = $client;
    }

    public function fetchWebsiteInformation(): array
    {
        $client = new CurlHttpClient();
        $html = $client->request('GET', 'https://sklep.swiatkwiatow.pl', [
            // ...
            'extra' => [
                'curl' => [
                    //CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V6,
                ],
            ],
        ]);
        $crawler = new Crawler($html->getContent());
        $images = $crawler->filterXPath('//img[contains(@class, "primary")]')->extract(['src', 'alt']);

        return $images;
    }
}
