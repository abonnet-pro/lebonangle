<?php

namespace App\Command;

use App\Repository\AdvertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:advert:delete:published',
    description: 'Delete advert published over days',
)]
class AdvertDeletePublishedCommand extends Command
{
    public function __construct(private EntityManagerInterface $manager, private AdvertRepository $advertRepository, string $name = null)
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

        $io->note(sprintf('Adverts published will be delete over %s days', $days));

        $adverts = $this->advertRepository->getAdvertsPublishedOverDays($days);

        if(count($adverts) === 0)
        {
            $io->info('There is no adverts to delete');
            return Command::SUCCESS;
        }

        foreach ($adverts as $advert)
        {
            $this->manager->remove($advert);
            $this->manager->flush();
            $io->success("Advert $advert deleted");
        }


        $io->success('Adverts deleted');

        return Command::SUCCESS;
    }
}
