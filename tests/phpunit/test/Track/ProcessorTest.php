<?php

namespace App\Tests\test\Track;

use App\Track\Processor;
use App\Entity\Track\Version;
use App\Entity\Track\Point;
use App\Entity\User\User;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;

/**
 * @covers App\Track\Processor
 */
class ProcessorTest extends TestCase
{
    /**
     * Test for non-xml file
     *
     * When passing non-xml file to simplexml_load_string a parser error
     * occurs. This can be catched as Warning exception.
     */
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

    /**
     * Test for non-gpx xml file
     *
     * The root node of a GPX file is <gpx>. If it missing we assume, that
     * the file is with invalid format.
     */
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

    /**
     * Test for corrupted gpx file
     *
     * Each <trkpt> element has lat and lon attributes. If one is not found,
     * the point should be skipped.
     */
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

    /**
     * Test for points count
     *
     * Pass valid input data and count processed points.
     */
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

    public function testProcessWaypoints()
    {
        $xml = '
        <gpx>
            <wpt lat="42.6928578" lon="24.4498812">
                <ele>738.0000000</ele>
                <name>01_test</name>
            </wpt>
            
            <wpt lat="42.6906328" lon="24.4460737">
                <ele>718.7058105</ele>
            </wpt>
        
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

        $wayPoints = $version->getWayPoints();
        $this->assertSame(2, $wayPoints->count());

        $firstPoint = $wayPoints[0];
        $secondPoint = $wayPoints[1];

        $this->assertSame(42.6928578, $firstPoint->getLat());
        $this->assertSame(24.4498812, $firstPoint->getLng());

        $this->assertSame(42.6906328, $secondPoint->getLat());
        $this->assertSame(24.4460737, $secondPoint->getLng());
    }

    public function processorInvalidValueProvider()
    {
        return [
            [ '<gpx><trk><trkseg>
                      <trkpt lat="90.1" lon="-122.0"></trkpt>
                      <trkpt lat="48.0" lon="-121.0"></trkpt>
                </trkseg></trk></gpx>'
            ],
            [ '<gpx><trk><trkseg>
                      <trkpt lat="47.0" lon="-122.0"></trkpt>
                      <trkpt lat="48.0" lon="-181.0"></trkpt>
                </trkseg></trk></gpx>'
            ]
        ];
    }

    /**
     * Test latitude/longitude values
     *
     * Longitude has range of -180 to +180 degreess, and latitude -90 to 90.
     * Pass invalid values and catch exception
     *
     * @dataProvider processorInvalidValueProvider
     */
    public function testProcessorInvalidValue(string $xml)
    {
        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $this->expectException(\UnexpectedValueException::class);
        $processor->process($xml, $version);
    }

    public function processorDistanceProvider()
    {
        return [
            [
                'a' => [
                    'lat' => -72.12503124983368,
                    'lon' => 140.76635256575747,
                ],
                'b' => [
                    'lat' => 57.853869742542884,
                    'lon' => -42.489069295117616,
                ],
                'expected' => 18421409.06561638
            ],
            [
                'a' => [
                    'lat' => 38.42570035324164,
                    'lon' => 112.5367830045156,
                ],
                'b' => [
                    'lat' => 37.17377431206806,
                    'lon' => 71.02303487909325,
                ],
                'expected' => 3619034.7619460933
            ],
            [
                'a' => [
                    'lat' => 1.9383769856308533,
                    'lon' => -97.62005322982051,
                ],
                'b' => [
                    'lat' => -53.0574428236386,
                    'lon' => -42.13740740171784,
                ],
                'expected' => 7977058.660106259
            ],
        ];
    }

    /**
     * Test distance between 2 points
     *
     * The expected distance between each 2 points are precalculated
     * using Haversine formula:
     * https://en.wikipedia.org/wiki/Haversine_formula
     *
     * @dataProvider processorDistanceProvider
     */
    public function testProcessorDisatance($a, $b, $expected)
    {
        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $xml = '<gpx><trk><trkseg>
            <trkpt lat="'.$a['lat'].'" lon="'.$a['lon'].'"></trkpt>
            <trkpt lat="'.$b['lat'].'" lon="'.$b['lon'].'"></trkpt>
        </trkseg></trk></gpx>';

        $processor->process($xml, $version);
        $this->assertEquals(
            round($version->getPoints()[1]->getDistance(), 5),
            round($expected, 5)
        );
    }
}
