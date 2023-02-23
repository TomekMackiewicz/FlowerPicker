<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Flower;

class FlowerPickerService
{
    private const NUMBER_OF_IMAGES = 3;
    private $entityManager;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ManagerRegistry $doctrine
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    public function fetchWebsiteInformation(): array
    {
        $images = $this->fetchImages();
        $flowersHashes = $this->getFlowersHashes();
        $this->removeDuplicates($images, $flowersHashes);
        $randomKeys = $this->pickRandomFlowers($images);

        // Save images
        $flowers = [];
        foreach ($randomKeys as $key) {
            $flower = new Flower();
            $flower->setSrc($images[$key][0]);
            $flower->setAlt($images[$key][1]);
            $flower->setHash(md5($images[$key][0]));
            $this->entityManager->persist($flower);
            $flowers[] = $flower;
        }

        foreach ($flowers as $flower) {
            $imageUrl = $flower->getSrc();
            @$rawImage = file_get_contents($imageUrl);
            if ($rawImage) {
                file_put_contents($flower->getAlt().'.jpg', $rawImage);
            }
        }

        $this->entityManager->flush();

        return $images;
    }

    /**
     * Fetch images from website
     * @return array
     */
    private function fetchImages(): array
    {
        $html = $this->client->request('GET', 'https://sklep.swiatkwiatow.pl', []);
        $crawler = new Crawler($html->getContent());

        return $crawler->filterXPath('//img[contains(@class, "primary")]')->extract(['src', 'alt']);
    }

    /**
     * Get downloaded images
     * @return array
     */
    private function getFlowersHashes(): array
    {
        return $this->doctrine->getRepository(Flower::class)->getHash();
    }

    /**
     * Check for duplicated images and remove if any
     * @param array $images
     * @param array $flowersHashes
     */
    private function removeDuplicates(array &$images, array $flowersHashes): void
    {
        foreach ($images as $key => $image) {
            if (in_array(md5($image[0]), $flowersHashes)) {
                unset($images[$key]);
            }
        }
    }

    /**
     * Pick random flowers
     * @param array $images
     * @return array
     */
    private function pickRandomFlowers(array $images): array
    {
        return count($images) < self::NUMBER_OF_IMAGES ?
            array_keys($images) : array_rand($images, self::NUMBER_OF_IMAGES);
    }

    private function saveImages()
    {

    }
}
