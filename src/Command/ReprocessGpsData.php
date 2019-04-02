<?php

namespace App\Command;

use App\Entity\Track;
use App\Track\Processor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReprocessGpsData extends Command
{
    protected static $defaultName = 'app:gps:reprocess';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @TODO use query and fetch tracks 1 by 1
        $repo = $this->em->getRepository(Track::class);
        $trackCollection = $repo->findAll();
        foreach ($trackCollection as $track) {
            $output->writeln("Processing track {$track->getId()}", OutputInterface::VERBOSITY_VERBOSE);

            /* @var $track Track */
            $track->prepareForRecalculation();
            $processor = new Processor();
            $processor->createTrack(
                $track->getFiles()->first()->getFileContent(),
                $track
            );

            $this->em->flush();
        }
    }
}
