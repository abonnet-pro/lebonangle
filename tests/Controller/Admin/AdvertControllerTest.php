<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Advert;
use App\Repository\AdminUserRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdvertControllerTest extends WebTestCase
{
    public function testIndexAdvert(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/advert');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Advert index');
    }

    public function testShowAdvert(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/advert/');
        $client->click($crawler->selectLink('show')->link());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Advert');
    }

    public function testPublishAdvert()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $category = $categoryRepository->findOneBy(
            ['name' => 'category']
        );

        $advert = new Advert();
        $advert->setTitle('test')
            ->setContent('content')
            ->setAuthor('author')
            ->setEmail('email@email0fr')
            ->setPrice(10)
            ->setCategory($category);
        $manager->persist($advert);
        $manager->flush();

        $crawler = $client->request('GET', '/admin/advert/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $client->click($crawler->selectLink('publish')->link());
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table tbody tr td:nth-of-type(11)', 'published');
    }

    public function testUnpublishAdvert()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $category = $categoryRepository->findOneBy(
            ['name' => 'category']
        );

        $advert = new Advert();
        $advert->setTitle('test')
            ->setContent('content')
            ->setAuthor('author')
            ->setEmail('email@email.fr')
            ->setPrice(10)
            ->setState('published')
            ->setCategory($category);
        $manager->persist($advert);
        $manager->flush();

        $crawler = $client->request('GET', '/admin/advert/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $client->click($crawler->selectLink('unpublish')->link());
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table tbody tr td:nth-of-type(11)', 'rejected');
    }

    public function testRejectAdvert()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $categoryRepository = static::getContainer()->get(CategoryRepository::class);
        $category = $categoryRepository->findOneBy(
            ['name' => 'category']
        );

        $advert = new Advert();
        $advert->setTitle('test')
            ->setContent('content')
            ->setAuthor('author')
            ->setEmail('email@email0fr')
            ->setPrice(10)
            ->setCategory($category);
        $manager->persist($advert);
        $manager->flush();

        $crawler = $client->request('GET', '/admin/advert/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $client->click($crawler->selectLink('reject')->link());
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table tbody tr td:nth-of-type(11)', 'rejected');
    }
}
