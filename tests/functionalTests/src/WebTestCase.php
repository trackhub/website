<?php

namespace App\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as Base;
use Symfony\Component\Process\Exception\ProcessFailedException;

class WebTestCase extends Base
{
    public function seedTestCase($case)
    {
        $seeder = new \Symfony\Component\Process\Process(
            './vendor/bin/phinx seed:run -s TestCase' . $case . 'Seeder',
            'script/migration',
        );

        try {
            $seeder->mustRun();
        } catch (ProcessFailedException $e) {
            echo $seeder->getOutput();
            echo $seeder->getErrorOutput();
            throw $e;
        }
    }
}
