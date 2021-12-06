<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Advert;
use App\Entity\Category;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CategoryControllerTest extends WebTestCase
{
    public function testAddCategory()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/category/');
        $crawler = $client->click($crawler->selectLink('Create new')->link());

        $button = $crawler->selectButton('Save');
        $form = $button->form();
        $form['category[name]'] = 'category';
        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Category saved');
    }

    public function testIndexCategory(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/category');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Category index');
    }

    public function testShowCategory(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $category = new Category();
        $category->setName('category');
        $manager->persist($category);
        $manager->flush();

        $crawler = $client->request('GET', '/admin/category/');
        $client->click($crawler->selectLink('show')->link());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table tbody tr:nth-of-type(2) td', 'category');
    }

    public function testEditCategory()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/category/');
        $crawler = $client->click($crawler->selectLink('edit')->link());

        $button = $crawler->selectButton('Update');
        $form = $button->form();
        $form['category[name]'] = 'category';
        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Category saved');
    }

    public function testDeleteCategoryOK()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $category = new Category();
        $category->setName('category');
        $manager->persist($category);
        $manager->flush();

        $crawler = $client->request('GET', '/admin/category/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $button = $crawler->selectButton('Delete');
        $form = $button->form();
        $client->submit($form);

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Category deleted');
    }

    public function testDeleteCategoryKO()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $category = new Category();
        $category->setName('category');
        $manager->persist($category);
        $manager->flush();
        $advert = new Advert();
        $advert->setTitle('test')
                ->setContent('content')
                ->setAuthor('author')
                ->setEmail('email@email0fr')
                ->setPrice(10)
                ->setCategory($category);
        $manager->persist($advert);
        $manager->flush();


        $crawler = $client->request('GET', '/admin/category/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $button = $crawler->selectButton('Delete');
        $form = $button->form();
        $client->submit($form);

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Cannot delete category');
    }
}
