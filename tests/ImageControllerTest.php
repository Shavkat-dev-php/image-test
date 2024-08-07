<?php

declare(strict_types=1);

namespace tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImageControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Image Upload'); // Adjust to your template content
    }

    public function testUploadImages(): void
    {
        $client = static::createClient();
        $client->request('POST', '/upload-images', ['url' => 'http://example.com/image.jpg']);
        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetSavedImages(): void
    {
        $client = static::createClient();
        $client->request('GET', '/get-saved-images');
        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
    }
}
