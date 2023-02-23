<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Doctrine\DBAL\Exception as DBALException;
use App\Entity\Flower;

class FlowerPickerService
{
    private const NUMBER_OF_IMAGES = 3;
    private const IMG_DIRECTORY = 'images';
    private const URL = 'https://sklep.swiatkwiatow.pl';
    private $entityManager;

    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly ManagerRegistry $doctrine,
        private readonly LoggerInterface $logger
    ) {
        $this->entityManager = $doctrine->getManager();
    }

    /**
     * Import images
     * @return bool
     */
    public function importImages(): bool
    {
        $imageData = $this->fetchFlowers();
        if (empty($imageData)) {
            $this->logger->warning('No images to import');
            return false;
        }
        $flowers = $this->prepareFlowersObjects($imageData);
        $flowersHashes = $this->getFlowersHashes();
        $this->removeDuplicates($flowers, $flowersHashes);
        $randomFlowers = $this->pickRandomFlowers($flowers);
        $imagesSaved = $this->saveImages($randomFlowers);
        if (false === $imagesSaved) {
            return false;
        }
        $this->uploadImages($randomFlowers);

        return true;
    }

    /**
     * Fetch images from website
     * @return array|bool
     */
    private function fetchFlowers(): array|bool
    {
        try {
            $html = $this->client->request('GET', self::URL, []);
            $crawler = new Crawler($html->getContent());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return $crawler->filterXPath('//img[contains(@class, "primary")]')->extract(['src', 'alt']);
    }

    /**
     * Prepare list of flowers objects
     * @param array $imageData
     * @return array
     */
    private function prepareFlowersObjects(array $imageData): array
    {
        $flowers = [];
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
     * @return bool
     */
    private function saveImages(array $randomFlowers): bool
    {
        try {
            foreach ($randomFlowers as $flower) {
                $this->entityManager->persist($flower);
            }
            $this->entityManager->flush();
        } catch (DBALException $e) {
            $this->logger->error($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Upload imported images
     * @param array $randomFlowers
     */
    private function uploadImages(array $randomFlowers): void
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
