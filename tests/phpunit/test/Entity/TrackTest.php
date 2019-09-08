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

        $track = new Track($user);
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
        $track = new Track(new User());

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
        $track = new Track(new User());

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
        $track = new Track(new User());

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
        $track = new Track(new User());

        $expectedVersions = [];

        $downhillVersionOne = $this->generateTrack(1);
        $expectedVersions[] = $downhillVersionOne->getVersions()[0];
        $downhillVersionOne->{$addTrackMethod}($track);

        $track->{$addTrackMethod}($downhillVersionOne);

        $this->assertArraysAreIdentical($expectedVersions, $track->{$getVersionsMethod}());
    }

    public function getNameDataProvider()
    {
        return [
            // only en with desired en
            ['text en', 'en', 'text en', null],
            // desired en, both en and bg are set
            ['text en', 'en', 'text en', 'text bg'],
            // only bg is set
            ['text bg', 'bg', null, 'text bg'],
            // desired en, but not set
            ['text bg', 'en', null, 'text bg'],
            // none set
            [null, 'en', null, null],
        ];
    }

    /**
     * @dataProvider getNameDataProvider
     */
    public function testGetName($exptected, $desiredLang,$nameEn, $nameBg)
    {
        $track = new Track(new User());
        $track->setNameBg($nameBg);
        $track->setNameEn($nameEn);

        $this->assertSame($exptected, $track->getName($desiredLang));
    }

    public function testToStringWithoutName()
    {
        $trackMockBuilder = $this->getMockBuilder(Track::class);
        $trackMockBuilder->disableOriginalConstructor();
        $trackMockBuilder->onlyMethods(['getId']);
        $trackMock = $trackMockBuilder->getMock();
        $trackMock->method('getId')->willReturn('d2c355ca-342a-4f74-b2d2-190d49b1ca5f');
        $this->assertSame('d2c355ca-342a-4f74-b2d2-190d49b1ca5f', $trackMock->__toString());
    }

    public function testToStringWithName()
    {
        $trackMockBuilder = $this->getMockBuilder(Track::class);
        $trackMockBuilder->disableOriginalConstructor();
        $trackMockBuilder->onlyMethods([]);
        $trackMock = $trackMockBuilder->getMock();
        $trackMock->setNameEn("some name");
        $this->assertSame('some name', $trackMock->__toString());
    }
}
