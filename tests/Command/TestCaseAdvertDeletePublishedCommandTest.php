<?php

namespace App\Tests\Command;

use App\Command\AdvertDeletePublishedCommand;
use App\Entity\Advert;
use App\Repository\AdvertRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TestCaseAdvertDeletePublishedCommandTest extends KernelTestCase
{
    public function testExecuteAdvertPublishedToDelete(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $advertRepository = static::getContainer()->get(AdvertRepository::class);
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
            ->setPublishedAt(new \DateTime())
            ->setCategory($category);
        $manager->persist($advert);
        $manager->flush();

        $application = new Application($kernel);
        $application->add(new AdvertDeletePublishedCommand($manager, $advertRepository));

        $command = $application->find('app:advert:delete:published');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(), 'days' => 1
        ));

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Adverts deleted',$output);
    }

    public function testExecuteNoAdvertPublishedToDelete(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $advertRepository = static::getContainer()->get(AdvertRepository::class);
        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $adverts = $advertRepository->findAll();
        foreach ($adverts as $advert)
        {
            $advert->setState('draft');
            $advert->setPublishedAt(null);
            $manager->flush();
        }

        $application = new Application($kernel);
        $application->add(new AdvertDeletePublishedCommand($manager, $advertRepository));

        $command = $application->find('app:advert:delete:published');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(), 'days' => 1
        ));

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('There is no adverts to delete', $output);
    }
}
