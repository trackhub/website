<?php

namespace App\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as Base;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WebTestCase extends Base
{
    public function seedTestCase($case)
    {
        $seeder = new \Symfony\Component\Process\Process(
            './vendor/bin/phinx seed:run -s CleanerSeeder -s UserSeeder -s TestCase' . $case . 'Seeder',
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
