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
                [],
            ],
            [
                '/en/gps/view/track-two',
                [],
            ],
            [
                '/en/gps/view/track-three',
                ['place-for-track-three-name'],
            ]
        ];
    }

    /**
     * @dataProvider trackViewDataProvider
     */
    public function testViewTrack(string $url, array $expectedcontent)
    {
        $client = static::createClient();

        $client->request('GET', $url);

        $this->assertEquals(
            200,
            $client->getResponse()->getStatusCode(),
        );

        $content = $client->getResponse()->getContent();
        foreach ($expectedcontent as $contentToMatch) {
            $this->assertStringContainsString($contentToMatch, $content);
        }
    }

    public function trackDownloadDataProvider()
    {
        return [
            [
                '/en/gps/download/track-one',
                [],
            ],
            [
                '/en/gps/download/track-two',
                [],
            ],
            [
                '/en/gps/download/track-three',
                ['place-for-track-three-name'],
            ],
        ];
    }

    /**
     * @dataProvider trackDownloadDataProvider
     */
    public function testDownloadLink(string $url, array $expectedcontent)
    {
        $client = static::createClient();

        $client->request('GET', $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = $client->getResponse()->getContent();
        foreach ($expectedcontent as $contentToMatch) {
            $this->assertStringContainsString($contentToMatch, $content);
        }
    }
}
