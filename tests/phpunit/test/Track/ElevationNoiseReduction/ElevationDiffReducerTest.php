<?php

namespace App\Tests\phpunit\test\Track\ElevationNoiseReduction;

use App\Entity\Track\Point;
use App\Track\ElevationNoiseReduction\ElevationDiffReducer;
use PHPUnit\Framework\TestCase;

class ElevationDiffReducerTest extends TestCase
{
    public static function shouldCountDataProvider(): array
    {
        return [
            [true, 3, 0, 4,],
            [true, 4, 0, 4,],
            [false, 5, 0, 4,],
        ];
    }

    /**
     * @dataProvider shouldCountDataProvider
     */
    public function testShouldCount(bool $expectedResult, float $minimumElevation, float $aElevation, float $bElevation)
    {
        $a = new Point(0, 1, 1);
        $a->setElevation($aElevation);
        $b = new Point(1, 1, 2);
        $b->setElevation($bElevation);

        $checker = new ElevationDiffReducer($minimumElevation);

        $this->assertSame(
            $expectedResult,
            $checker->shouldCount($a, $b),
        );
    }
}
