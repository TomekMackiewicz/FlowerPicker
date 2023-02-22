<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class FlowerPickerService
{
    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    public function fetchWebsiteInformation(): array
    {
        $html = $this->client->request('GET', 'https://sklep.swiatkwiatow.pl', []);
        $crawler = new Crawler($html->getContent());
        $images = $crawler->filterXPath('//img[contains(@class, "primary")]')->extract(['src', 'alt']);

        return $images;
    }
}
