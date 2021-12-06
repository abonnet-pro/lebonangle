<?php

namespace App\Tests\Controller\Admin;

use App\Repository\AdminUserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/login/');

        $crawler = $client->followRedirect();

        $button = $crawler->selectButton('Sign in');
        $form = $button->form();
        $form['email'] = 'admin@admin.fr';
        $form['password'] = '1234';
        $client->submit($form);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'ADMINISTRATION');
    }

    public function testLogout(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(AdminUserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@admin.fr');
        $client->loginUser($testUser);

        $client->request('GET', '/admin/logout/');
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Please sign in');
    }
}
