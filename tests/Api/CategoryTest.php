<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Advert;
use App\Entity\Category;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;

class CategoryTest extends ApiTestCase
{
    public function testGetAllCategories()
    {
        $response = static::createClient()->request('GET', '/categories');

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => '/categories',
            '@context' => '/contexts/Category',
            '@type' => 'hydra:Collection'
            ]);
        self::assertMatchesResourceCollectionJsonSchema(Category::class);
    }

    public function testGetOneCategory()
    {
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $category = $categoryRepository->findOneBy([
            'name' => 'category'
        ]);
        $id = $category->getId();
        $response = static::createClient()->request('GET', "/categories/$id");

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@id' => "/categories/$id",
            '@context' => '/contexts/Category',
            '@type' => 'Category'
        ]);
        self::assertMatchesResourceItemJsonSchema(Category::class);
    }
}