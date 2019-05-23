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

    public function testGetDownhillVersions()
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];

        $track->addDownhill($downhillVersionOne);

        $this->assertArraysAreIdentical($expectedVersions, $track->getDownhillVersions());
    }

    public function testGetDownhillVersionsMultiple()
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];

        $dhVersionTwo = $this->generateTrack(1);
        $expectedVersions[] = $dhVersionTwo->getVersions()[0];

        $track->addDownhill($downhillVersionOne);
        $track->addDownhill($dhVersionTwo);

        $this->assertArraysAreIdentical($expectedVersions, $track->getDownhillVersions());
    }

    public function testGetDownhillVersionsRecursive()
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];

        $dhVersionTwo = $this->generateTrack(1);
        $expectedVersions[] = $dhVersionTwo->getVersions()[0];

        $dhVersionTwoRecursive = $this->generateTrack(1);
        $dhVersionTwo->addDownhill($dhVersionTwoRecursive);
        $expectedVersions[] = $dhVersionTwoRecursive->getVersions()[0];

        $track->addDownhill($downhillVersionOne);
        $track->addDownhill($dhVersionTwo);

        $this->assertArraysAreIdentical($expectedVersions, $track->getDownhillVersions());
    }

    public function testGetDownhillVersionsInfinitiveLoop()
    {
        $track = new Track();

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];
        $downhillVersionOne->addDownhill($track);

        $track->addDownhill($downhillVersionOne);

        $this->assertArraysAreIdentical($expectedVersions, $track->getDownhillVersions());
    }
}
