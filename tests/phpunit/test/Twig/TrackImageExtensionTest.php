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
        $track = new Track($user);
        $image = new Track\Image('my-pic.jpg', $user, $track);
        $thumbnailUrl = $extension->resize($image, 300, 400);
        $this->assertSame('/my/secret/dir/300/400/my-pic.jpg', $thumbnailUrl);
    }
}
