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
    private const IMG_DIRECTORY = 'images';
    private $entityManager;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ManagerRegistry $doctrine
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    public function fetchWebsiteInformation(): bool
    {
        $flowers = $this->fetchFlowers();
        $flowersHashes = $this->getFlowersHashes();
        $this->removeDuplicates($flowers, $flowersHashes);
        $randomFlowers = $this->pickRandomFlowers($flowers);
        $this->saveImages($randomFlowers);
        $this->uploadImages($randomFlowers);

        return true;
    }

    /**
     * Fetch images from website
     * @return array
     */
    private function fetchFlowers(): array
    {
        $flowers = [];
        $html = $this->client->request('GET', 'https://sklep.swiatkwiatow.pl', []);
        $crawler = new Crawler($html->getContent());
        $imageData = $crawler->filterXPath('//img[contains(@class, "primary")]')->extract(['src', 'alt']);
        foreach ($imageData as $image) {
            $flower = new Flower();
            $flower->setSrc($image[0]);
            $flower->setAlt($image[1]);
            $flower->setHash(md5($image[0]));
            $flowers[] = $flower;
        }

        return $flowers;
    }

    /**
     * Get downloaded images hashes
     * @return array
     */
    private function getFlowersHashes(): array
    {
        return $this->doctrine->getRepository(Flower::class)->getHash();
    }

    /**
     * Check for duplicated images and remove if any
     * @param array $flowers
     * @param array $flowersHashes
     */
    private function removeDuplicates(array &$flowers, array $flowersHashes): void
    {
        foreach ($flowers as $key => $flower) {
            if (in_array($flower->getHash(), $flowersHashes)) {
                unset($flowers[$key]);
            }
        }
    }

    /**
     * Pick random flowers
     * @param array $flowers
     * @return array
     */
    private function pickRandomFlowers(array $flowers): array
    {
        $count = count($flowers) < self::NUMBER_OF_IMAGES ? count($flowers) : self::NUMBER_OF_IMAGES;
        shuffle($flowers);

        return array_slice($flowers, 0, $count);
    }

    /**
     * Save imported images to database
     * @param array $randomFlowers
     */
    private function saveImages(array $randomFlowers): void
    {
        foreach ($randomFlowers as $flower) {
            $this->entityManager->persist($flower);
        }
        $this->entityManager->flush();
    }

    /**
     * Upload imported images
     * @param array $randomFlowers
     */
    private function uploadImages($randomFlowers): void
    {
        if (!file_exists(self::IMG_DIRECTORY)) {
            mkdir(self::IMG_DIRECTORY, 0755);
        }
        foreach ($randomFlowers as $flower) {
            $imageUrl = $flower->getSrc();
            $imageExtension = explode(".", $imageUrl);
            $imageExtension = end($imageExtension);
            @$rawImage = file_get_contents($imageUrl);
            if ($rawImage) {
                file_put_contents(self::IMG_DIRECTORY.'/'.$flower->getAlt().'.'.$imageExtension, $rawImage);
            }
        }
    }
}
