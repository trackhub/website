<?php

namespace App\Tests\phpunit\test\Twig;

use App\Entity\Track;
use App\Entity\User\User;
use App\Twig\TrackImageExtension;
use PHPUnit\Framework\TestCase;

class TrackImageExtensionTest extends TestCase
{
    public function testResize()
    {
        $user = new User();

        $extension = new TrackImageExtension('/my/secret/dir');
        $trackMockBuilder = $this->getMockBuilder(Track::class);
        $trackMockBuilder->setConstructorArgs([$user]);
        $trackMockBuilder->onlyMethods(['getId']);
        $trackMock = $trackMockBuilder->getMock();
        $trackMock->expects($this->any())->method('getId')->willReturn('1234-567');

        $image = new Track\Image('my-pic.jpg', $user, $trackMock);
        $thumbnailUrl = $extension->resize($image, 300, 400);
        $this->assertSame('/my/secret/dir/300/400/my-pic.jpg', $thumbnailUrl);
    }
}
