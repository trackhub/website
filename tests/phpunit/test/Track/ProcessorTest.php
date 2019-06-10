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
        $xml = '
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc
            accumsan leo quis arcu convallis faucibus. Mauris pharetra dolor
            faucibus, ultrices ex in, porttitor urna. Aliquam erat volutpat.
            Curabitur consectetur purus ex, at porta ex viverra in. Nullam
            rutrum sem quis lacus pellentesque rutrum. Quisque vitae arcu eros.
            Maecenas luctus enim at vehicula varius. In lacinia lorem vehicula
            elementum fermentum. Aenean venenatis ac massa eu maximus. Lorem
            ipsum dolor sit amet, consectetur adipiscing elit. Quisque et
            varius eros.
            ';

        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $this->expectException(Warning::class);
        $processor->process($xml, $version);
    }

    public function testProcessorInvalidFormat()
    {
        $xml = '
            <dummy>
            </dummy>
        ';

        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $this->expectException(\RuntimeException::class);
        $processor->process($xml, $version);
	}

    public function testProcessorCorrupedFile()
    {
        /**
         * Sample document from here:
         * https://en.wikipedia.org/wiki/GPS_Exchange_Format#Sample_GPX_document
         */
        $xml = '
        <gpx>
          <trk>
            <name>Example GPX Document</name>
            <trkseg>
              <trkpt lat="47.644548" lon="-122.326897">
                <ele>4.46</ele>
                <time>2009-10-17T18:37:26Z</time>
              </trkpt>
              <trkpt lat="47.644548" lon="-122.326897">
                <ele>4.94</ele>
                <time>2009-10-17T18:37:31Z</time>
              </trkpt>
              <trkpt lat="47.644548">
                <ele>6.87</ele>
                <time>2009-10-17T18:37:34Z</time>
              </trkpt>
            </trkseg>
          </trk>
        </gpx>
        ';

        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $processor->process($xml, $version);
        $this->assertCount(2, $version->getPoints());
    }

    public function testProcessorCount()
    {
        /**
         * Sample document from here:
         * https://en.wikipedia.org/wiki/GPS_Exchange_Format#Sample_GPX_document
         */
        $xml = '
        <gpx>
          <trk>
            <name>Example GPX Document</name>
            <trkseg>
              <trkpt lat="47.644548" lon="-122.326897">
                <ele>4.46</ele>
                <time>2009-10-17T18:37:26Z</time>
              </trkpt>
              <trkpt lat="47.644548" lon="-122.326897">
                <ele>4.94</ele>
                <time>2009-10-17T18:37:31Z</time>
              </trkpt>
              <trkpt lat="47.644548" lon="-122.326897">
                <ele>6.87</ele>
                <time>2009-10-17T18:37:34Z</time>
              </trkpt>
            </trkseg>
          </trk>
        </gpx>
        ';

        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $processor->process($xml, $version);
        $this->assertCount(3, $version->getPoints());
    }
}
