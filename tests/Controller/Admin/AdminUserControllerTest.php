<?php

namespace App\Tests\Controller\Admin;

use App\Entity\AdminUser;
use App\Repository\AdminUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminUserControllerTest extends WebTestCase
{
    public function testAddAdmin()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/user/');
        $crawler = $client->click($crawler->selectLink('Create new')->link());

        $button = $crawler->selectButton('Save');
        $form = $button->form();
        $random = random_int(1, 10);
        $form['admin_user[email]'] = "email$random@admin.fr";
        $form['admin_user[username]'] = 'newAdmin';
        $form['admin_user[plainPassword]'] = '1234';
        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Admin saved');
    }

    public function testIndexAdmin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/user');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'AdminUser index');
    }

    public function testShowAdmin(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/user/');
        $client->click($crawler->selectLink('show')->link());

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'AdminUser');
    }

    public function testEditAdmin()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/admin/user/');
        $crawler = $client->click($crawler->selectLink('edit')->link());

        $button = $crawler->selectButton('Update');
        $form = $button->form();
        $form['admin_user[username]'] = 'EditAdmin';
        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table tbody tr td:nth-of-type(2)', 'EditAdmin');
    }

    public function testDeleteAdminOK()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $admin = new AdminUser();
        $random = random_int(1, 10);
        $admin->setEmail("admin$random@email.fr")
            ->setPlainPassword('1234')
            ->setUsername('admin');
        $manager->persist($admin);
        $manager->flush();

        $crawler = $client->request('GET', '/admin/user/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $button = $crawler->selectButton('Delete');
        $form = $button->form();
        $client->submit($form);

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Admin deleted');
    }

    public function testDeleteAdminKO()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $adminRepository = static::getContainer()->get(AdminUserRepository::class);
        $queryBuilder = $adminRepository->createQueryBuilder('a');
        $queryBuilder->where('a.email != :email')
            ->setParameter('email', 'admin@admin.fr');

        $admins = $queryBuilder->getQuery()->getResult();

        foreach ($admins as $admin)
        {
            $manager->remove($admin);
            $manager->flush();
        }

        $crawler = $client->request('GET', '/admin/user/');
        $crawler = $client->click($crawler->selectLink('show')->link());
        $button = $crawler->selectButton('Delete');
        $form = $button->form();
        $client->submit($form);

        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.alert', 'Cannot delete admin');
    }
}
