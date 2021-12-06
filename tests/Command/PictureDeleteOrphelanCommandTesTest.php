<?php

namespace App\Tests;

use App\Command\AdvertDeletePublishedCommand;
use App\Command\PictureDeleteOrphelanCommand;
use App\Entity\Picture;
use App\Repository\AdvertRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class PictureDeleteOrphelanCommandTesTest extends KernelTestCase
{
    public function testExecutePicturesOrphanToDelete(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $picturesRepository = static::getContainer()->get(PictureRepository::class);
        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $picture = new Picture();
        $picture->setPath('test.jpg');
        $manager->persist($picture);
        $manager->flush();

        $application = new Application($kernel);
        $application->add(new PictureDeleteOrphelanCommand($manager, $picturesRepository));

        $command = $application->find('app:picture:delete:orphan');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(), 'days' => 1
        ));

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Pictures deleted',$output);
    }

    public function testExecuteNoPicturesOrphanToDelete(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $picturesRepository = static::getContainer()->get(PictureRepository::class);
        $manager = static::getContainer()->get(EntityManagerInterface::class);

        $pictures = $picturesRepository->findAll();
        foreach ($pictures as $picture)
        {
            $manager->remove($picture);
            $manager->flush();
        }

        $application = new Application($kernel);
        $application->add(new PictureDeleteOrphelanCommand($manager, $picturesRepository));

        $command = $application->find('app:picture:delete:orphan');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(), 'days' => 1
        ));

        $output = $commandTester->getDisplay();

        self::assertStringContainsString('There is no pictures to delete', $output);
    }
}
