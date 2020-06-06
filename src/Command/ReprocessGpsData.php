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

    private $processor;

    public function __construct(EntityManagerInterface $em, Processor $processor)
    {
        $this->em = $em;
        $this->processor = $processor;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $processor = $this->processor;
        // @TODO use query and fetch tracks 1 by 1
        $repo = $this->em->getRepository(Track::class);
        $trackCollection = $repo->findAll();
        foreach ($trackCollection as $track) {
            /* @var $track Track */
            $output->writeln("Processing track {$track->getId()}", OutputInterface::VERBOSITY_VERBOSE);

            $track->prepareForRecalculation();

            $optimizedVersionIndex = 0;
            foreach ($track->getVersions() as $versionIndex => $version) {
                $output->writeln("Processing version {$versionIndex}");

                $processor->process(
                    $version->getFile()->getFileContent(),
                    $version
                );

                foreach ($processor->generateOptimizedPoints($version) as $optimizedPoint) {
                    $optimizedPoint->setVersionIndex($optimizedVersionIndex);
                    $track->addOptimizedPoint($optimizedPoint);
                }

                $optimizedVersionIndex++;
            }

            $processor->postProcess($track);

            $this->em->flush();
        }

        return 0;
    }
}
