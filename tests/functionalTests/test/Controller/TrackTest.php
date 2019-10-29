<?php

namespace App\Tests\Controller;

use App\Tests\functionalTests\test\Helper\UserGenerator;
use App\Test\WebTestCase;

class TrackTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seedTestCase('User', 'TestCaseOne');
    }

    public function trackViewDataProvider()
    {
        return [
            [
                '/en/gps/view/track-one',

            ],
            [
                '/en/gps/view/track-two',
            ],
        ];
    }

    /**
     * @dataProvider trackViewDataProvider
     */
    public function testViewTrack($url)
    {
        $client = static::createClient();

        $client->request('GET', $url);

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
        );
    }

    public function trackDownloadDataProvider()
    {
        return [
            [
                '/en/gps/download/track-one',
            ],
            [
                '/en/gps/download/track-two',
            ],
        ];
    }

    /**
     * @dataProvider trackDownloadDataProvider
     */
    public function testDownloadLink($url)
    {
        $client = static::createClient();

        $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
