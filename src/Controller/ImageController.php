<?php

declare(strict_types=1);

namespace App\Controller;

use GuzzleHttp\Exception\GuzzleException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client as GuzzleClient;
use Symfony\Component\DomCrawler\Crawler;

class ImageController extends AbstractController
{
    const IMAGE_DIR = 'images/';
    const IMAGE_FILE = 'public/images/images.json';

    /**
     * @Route("/", name="image_upload", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('image_upload.html.twig');
    }

    /**
     * @Route("/upload-images", name="upload_images", methods={"POST"})
     * @throws GuzzleException
     */
    public function uploadImages(Request $request): JsonResponse
    {
        $url = $request->request->get('url');
        $images = $this->fetchAndProcessImages($url);

        $savedImages = $this->saveImages($images);

        return new JsonResponse(['images' => $savedImages]);
    }

    /**
     * @Route("/get-saved-images", name="get_saved_images", methods={"GET"})
     */
    public function getSavedImages(): JsonResponse
    {
        $images = $this->retrieveSavedImages();
        return new JsonResponse(['images' => $images]);
    }

    /**
     * @throws GuzzleException
     */
    private function fetchAndProcessImages(string $url): array
    {
        $client = new GuzzleClient();
        $response = $client->request('GET', $url);
        $html = $response->getBody()->getContents();

        $crawler = new Crawler($html);
        $imageUrls = $crawler->filter('img')->each(function (Crawler $node) {
            return $node->attr('src');
        });

        $processedImages = [];
        foreach ($imageUrls as $imageUrl) {
            $imagePath = $this->processImage($imageUrl);
            if ($imagePath) {
                $processedImages[] = [
                    'src' => '/images/' . basename($imagePath),
                    'alt' => basename($imagePath)
                ];
            }
        }

        return $processedImages;
    }

    private function processImage(string $imageUrl): ?string
    {
        $imageContent = file_get_contents($imageUrl);
        if ($imageContent === false) {
            return null;
        }

        $imageName = uniqid() . '.jpg';
        $imagePath = self::IMAGE_DIR . $imageName;
        $fullPath = 'public/' . $imagePath;


        $sourceImage = imagecreatefromstring($imageContent);
        if ($sourceImage === false) {
            return null;
        }


        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);


        $newHeight = 200;
        $newWidth = 200;


        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);


        $textColor = imagecolorallocate($newImage, 255, 255, 255);
        $fontPath = 'path/to/arial.ttf';
        imagettftext($newImage, 15, 0, 10, $newHeight - 10, $textColor, $fontPath, 'Sample Text');


        if (imagejpeg($newImage, $fullPath)) {
            imagedestroy($newImage);
            imagedestroy($sourceImage);
            return $imagePath;
        }

        imagedestroy($newImage);
        imagedestroy($sourceImage);
        return null;
    }

    private function saveImages(array $images): array
    {
        $this->updateImageFile($images);
        return $images;
    }

    private function retrieveSavedImages(): array
    {
        if (!file_exists(self::IMAGE_FILE)) {
            return [];
        }
        $json = file_get_contents(self::IMAGE_FILE);
        return json_decode($json, true) ?: [];
    }

    private function updateImageFile(array $images): void
    {
        file_put_contents(self::IMAGE_FILE, json_encode($images));
    }
}
