<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Advert;
use App\Entity\Category;
use App\Entity\Picture;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureTest extends ApiTestCase
{
    public function testGetAllPictures()
    {
        $response = static::createClient()->request('GET', '/pictures');

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => '/pictures',
            '@context' => '/contexts/Picture',
            '@type' => 'hydra:Collection'
            ]);
        self::assertMatchesResourceCollectionJsonSchema(Picture::class);
    }

    public function testGetOnePicture()
    {
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $pictureRepository = static::getContainer()->get(PictureRepository::class);

        $picture = new Picture();
        $picture->setPath('linux.jpg');
        $manager->persist($picture);
        $manager->flush();

        $picture = $pictureRepository->findOneBy([
            'path' => 'linux.jpg'
        ]);
        $id = $picture->getId();
        $response = static::createClient()->request('GET', "/pictures/$id");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => "/pictures/$id",
            '@context' => '/contexts/Picture',
            '@type' => 'https://schema.org/MediaObject'
        ]);
        self::assertMatchesResourceItemJsonSchema(Category::class);
    }

    public function testPostPicture()
    {
        $file = new UploadedFile(
            __DIR__.'/../pictures/linux.jpg',
            'linux.jpg',
            'linux/jpg',
        );

        $response = static::createClient()->request('POST', '/pictures', [
            'headers' => ['Content-Type' => 'multipart/form-data'],
            'extra' => [
                'files' => ['file' => $file]
            ]
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains(['path' => 'linux.jpg']);
    }
}