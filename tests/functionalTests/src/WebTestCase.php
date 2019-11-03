<?php

namespace App\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as Base;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WebTestCase extends Base
{
    public function seedTestCase(...$cases)
    {
        foreach ($cases as &$case) {
            $case = '-s ' . $case . 'Seeder';
        }

        $seedersAsString = implode(' ', $cases);

        $seeder = new \Symfony\Component\Process\Process(
            './vendor/bin/phinx seed:run -s CleanerSeeder ' . $seedersAsString,
            'script/migration',
        );

        $processCommand = new \Symfony\Component\Process\Process(
            './bin/console app:gps:reprocess',
        );

        try {
            $seeder->mustRun();
            $processCommand->mustRun();
        } catch (ProcessFailedException $e) {
            echo $seeder->getOutput();
            echo $seeder->getErrorOutput();
            throw $e;
        }
    }
}
