<?php

namespace App\Command;

use App\Entity\Gps;
use App\Track\Processor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReprocessGpsData extends Command
{
    protected static $defaultName = 'app:gps:reprocess';

    private $em;

    /**
     * ReprocessGpsData constructor.
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @TODO use query and fetch track 1 by 1
        $repo = $this->em->getRepository(Gps::class);
        $trackCollection = $repo->findAll();
        foreach ($trackCollection as $track) {
            $output->writeln("Processing track {$track->getId()}", OutputInterface::VERBOSITY_VERBOSE);

            /* @var $track Gps */
            $track->prepareForRecalculation();
            $processor = new Processor();
            $processor->process(
                $track->getFiles()->first()->getFileContent(),
                $track
            );

            $this->em->flush();
        }
    }
}