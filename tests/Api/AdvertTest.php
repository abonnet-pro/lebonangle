<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Advert;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;

class AdvertTest extends ApiTestCase
{
    public function testGetAllAdverts()
    {
        $response = static::createClient()->request('GET', '/adverts');

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => '/adverts',
            '@context' => '/contexts/Advert',
            '@type' => 'hydra:Collection'
            ]);
        self::assertMatchesResourceCollectionJsonSchema(Advert::class);
    }

    public function testGetOneAdvert()
    {
        $advertRepository = static::getContainer()->get(AdvertRepository::class);
        $advert = $advertRepository->findOneBy([
            'title' => 'advert'
        ]);
        $id = $advert->getId();
        $response = static::createClient()->request('GET', "/adverts/$id");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => "/adverts/$id",
            '@context' => '/contexts/Advert',
            '@type' => 'Advert'
        ]);
        self::assertMatchesResourceItemJsonSchema(Advert::class);
    }

    public function testPostAdvert()
    {
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $category = $categoryRepository->findOneBy([
            'name' => 'category'
        ]);
        $id = $category->getId();
        $response = static::createClient()->request('POST', '/adverts', ['json' => [
            "title" => "testTitle",
            "content" => "testContent",
            "author" => "testAuthor",
            "email" => "test@email.fr",
            "category" => "/categories/$id",
            "price" => 10
        ]]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'category' => "/categories/$id",
            '@context' => '/contexts/Advert',
            '@type' => 'Advert'
        ]);
        self::assertMatchesResourceItemJsonSchema(Advert::class);
    }
}