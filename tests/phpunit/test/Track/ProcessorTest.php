<?php

namespace App\Tests\test\Track;

use App\Track\Processor;
use App\Entity\Track\Version;
use App\Entity\User\User;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;


class ProcessorTest extends TestCase
{
    public function testProcessorInvalidFile()
    {
        $xml = "
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc 
            accumsan leo quis arcu convallis faucibus. Mauris pharetra dolor 
            faucibus, ultrices ex in, porttitor urna. Aliquam erat volutpat. 
            Curabitur consectetur purus ex, at porta ex viverra in. Nullam 
            rutrum sem quis lacus pellentesque rutrum. Quisque vitae arcu eros.
            Maecenas luctus enim at vehicula varius. In lacinia lorem vehicula
            elementum fermentum. Aenean venenatis ac massa eu maximus. Lorem 
            ipsum dolor sit amet, consectetur adipiscing elit. Quisque et 
            varius eros.
            ";

        $user = new User();

        $processor = new Processor();
        $version = new Version($user);

        $this->expectException(Warning::class);
        $processor->process($xml, $version);
    }
}
