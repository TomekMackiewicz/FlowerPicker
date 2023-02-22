<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class FlowerPickerService
{
    private const NUMBER_OF_IMAGES = 3;

    public function __construct(
        private readonly HttpClientInterface $client
    ) {
    }

    public function fetchWebsiteInformation(): array
    {
        $html = $this->client->request('GET', 'https://sklep.swiatkwiatow.pl', []);
        $crawler = new Crawler($html->getContent());
        $images = $crawler->filterXPath('//img[contains(@class, "primary")]')->extract(['src', 'alt']);

        $randomKeys = array_rand($images, self::NUMBER_OF_IMAGES);
        $randomImages = [];

        foreach ($randomKeys as $key) {
            $randomImages[] = $images[$key];
        }
        
dd($randomImages);
        return $images;
    }

    // Dodanie do listy wylosowanych kwiatów

    // Pobranie listy już wybranych kwiatów

    // Wylosowanie 3 wcześniej niewybranych kwiatów
}
