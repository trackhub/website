<?php

namespace App\Tests\test\Entity;

use App\Entity\User\User;
use PHPUnit\Framework\TestCase;
use App\Entity\Track;

class TrackTest extends TestCase
{
    private function generateTrack($versionsCount = 1)
    {
        $user = new User();

        $track = new Track();
        for ($q = 0; $q < $versionsCount; $q++) {
            $version = new Track\Version($user);
            $track->addVersion($version);
        }

        return $track;
    }

    private function assertArraysAreIdentical($a, $b)
    {
        foreach ($a as $aKey => $aValue) {
            $this->assertNotFalse(
                array_search($aValue, $b),
                'Arrays not equal, key ' . $aKey . ' not found in $b'
            );
        }

        foreach ($b as $bkey => $bValue) {
            $this->assertNotFalse(
                array_search($bValue, $a),
                'Arrays not equal, key ' . $bkey . ' not found in $a'
            );
        }
    }

    public function downhillUphillMethodProvider()
    {
        return [
            ['addDownhill', 'getDownhillVersions',],
            ['addUphill', 'getUphillVersions',],
        ];
    }

    /**
     * @dataProvider downhillUphillMethodProvider
     */
    public function testGetDownhillVersions($addTrackMethod, $getVersionsMethod)
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];

        $track->{$addTrackMethod}($downhillVersionOne);

        $this->assertArraysAreIdentical($expectedVersions, $track->{$getVersionsMethod}());
    }

    /**
     * @dataProvider downhillUphillMethodProvider
     */
    public function testGetDownhillVersionsMultiple($addTrackMethod, $getVersionsMethod)
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(2);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];
        $expectedVersions[] = $downhillVersionOne->getVersions()[1];

        $dhVersionTwo = $this->generateTrack(1);
        $expectedVersions[] = $dhVersionTwo->getVersions()[0];

        $track->{$addTrackMethod}($downhillVersionOne);
        $track->{$addTrackMethod}($dhVersionTwo);

        $this->assertArraysAreIdentical($expectedVersions, $track->{$getVersionsMethod}());
    }

    /**
     * @dataProvider downhillUphillMethodProvider
     */
    public function testGetDownhillVersionsRecursive($addTrackMethod, $getVersionsMethod)
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];

        $dhVersionTwo = $this->generateTrack(1);
        $expectedVersions[] = $dhVersionTwo->getVersions()[0];

        $dhVersionTwoRecursive = $this->generateTrack(1);
        $dhVersionTwo->{$addTrackMethod}($dhVersionTwoRecursive);
        $expectedVersions[] = $dhVersionTwoRecursive->getVersions()[0];

        $track->{$addTrackMethod}($downhillVersionOne);
        $track->{$addTrackMethod}($dhVersionTwo);

        $this->assertArraysAreIdentical($expectedVersions, $track->{$getVersionsMethod}());
    }

    /**
     * @dataProvider downhillUphillMethodProvider
     */
    public function testGetDownhillVersionsInfinitiveLoop($addTrackMethod, $getVersionsMethod)
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];
        $downhillVersionOne->{$addTrackMethod}($track);

        $track->{$addTrackMethod}($downhillVersionOne);

        $this->assertArraysAreIdentical($expectedVersions, $track->{$getVersionsMethod}());
    }
}
