<?php

namespace App\Command;

use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:picture:delete:orphan',
    description: 'Delete orphan pictures over days',
)]
class PictureDeleteOrphelanCommand extends Command
{
    public function __construct(private EntityManagerInterface $manager, private PictureRepository $pictureRepository, string $name = null)
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('days', InputArgument::REQUIRED, 'Number of days deletion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = $input->getArgument('days');

        $io->note(sprintf('Pictures orphan will be delete over %s days', $days));

        $pictures = $this->pictureRepository->getPicturesOrphanOverDays($days);

        if(count($pictures) === 0)
        {
            $io->info('There is no pictures to delete');
            return Command::SUCCESS;
        }

        foreach ($pictures as $picture)
        {
            $this->manager->remove($picture);
            $this->manager->flush();
            $io->success("Pictures $picture deleted");
        }

        $io->success('Pictures deleted');

        return Command::SUCCESS;
    }
}
