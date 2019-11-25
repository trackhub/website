<?php

namespace App\Tests\phpunit\test\Track\TwoPointsChecker;

use App\Entity\Track\Point;
use App\Track\TwoPointsChecker\ElevationChecker;
use PHPUnit\Framework\TestCase;

class ElevationCheckerTest extends TestCase
{
    public function pointsDataProvider()
    {
        return [
            [
                true,
                30,
                24.763194,
                42.138433,
                0,
                24.763224,
                42.138385,
                3,
            ],
            [
                false,
                1,
                24.763194,
                42.138433,
                0,
                24.763224,
                42.138385,
                10,
            ],
            [ // case for >=
                false,
                10,
                24.763194,
                42.138433,
                0,
                24.763224,
                42.138385,
                1.03744279582964,
            ],
        ];
    }

    /**
     * @dataProvider pointsDataProvider
     */
    public function testIsRealPoint(bool $result, float $maxAngle, float $latA, float $lngA, float $elevA, float $latB, float $lngB, float $elevB)
    {
        $pointA = new Point(0, $latA, $lngA);
        $pointA->setElevation($elevA);

        $pointB = new Point(1, $latB, $lngB);
        $pointB->setElevation($elevB);

        $checker = new ElevationChecker($maxAngle);
        $this->assertSame($result, $checker->isReal($pointA, $pointB));
    }
}
