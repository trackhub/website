<?php

namespace App\Tests\phpunit\test\Track;

use App\Entity\Track\Version;
use App\Entity\User\User;
use App\Track\Processor;
use PHPUnit\Framework\TestCase;

class ProcessorElevationTest extends TestCase
{
    public static function elevationTestCases()
    {
        return [
            [
                '<gpx><trk><trkseg>
                    <trkpt lat="40" lon="40"><ele>100</ele></trkpt>
                    <trkpt lat="40.01" lon="40"></trkpt>
                    <trkpt lat="40.02" lon="40"><ele>110</ele></trkpt>
                </trkseg></trk></gpx>',
                10,
                0,
            ],
            [
                '<gpx><trk><trkseg>
                    <trkpt lat="40" lon="40"></trkpt>
                    <trkpt lat="40.01" lon="40"></trkpt>
                    <trkpt lat="40.02" lon="40"><ele>110</ele></trkpt>
                    <trkpt lat="40.02" lon="40"><ele>115</ele></trkpt>
                </trkseg></trk></gpx>',
                5,
                0,
            ],
            [
                '<gpx><trk><trkseg>
                    <trkpt lat="40" lon="40"></trkpt>
                    <trkpt lat="40.01" lon="40"></trkpt>
                    <trkpt lat="40.02" lon="40"><ele>110</ele></trkpt>
                    <trkpt lat="40.02" lon="40"></trkpt>
                </trkseg></trk></gpx>',
                0,
                0,
            ],
            [
                '<gpx><trk><trkseg>
                    <trkpt lat="40" lon="40"></trkpt>
                    <trkpt lat="40.01" lon="40"><ele>110</ele></trkpt>
                    <trkpt lat="40.02" lon="40"></trkpt>
                    <trkpt lat="40.03" lon="40"><ele>112</ele></trkpt>
                    <trkpt lat="40.04" lon="40"></trkpt>
                    <trkpt lat="40.05" lon="40"><ele>108</ele></trkpt>
                </trkseg></trk></gpx>',
                2,
                4,
            ],
        ];
    }

    /**
     * @dataProvider elevationTestCases
     */
    public function testPointNullableElevation($xml, $expectedPositiveElev, $expectedNegativeElev)
    {
        $processor = new Processor();
        $user = new User();
        $version = new Version($user);

        $processor->process($xml, $version);

        $this->assertSame($expectedPositiveElev, $version->getPositiveElevation());
        $this->assertSame($expectedNegativeElev, $version->getNegativeElevation());
    }
}
