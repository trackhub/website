<?php

namespace App\Tests\Controller;

use App\Tests\functionalTests\test\Helper\UserGenerator;
use App\Test\WebTestCase;

class TrackTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->seedTestCase('One');
    }

    public function testViewTrack()
    {
        $client = static::createClient();

        $client->request('GET', '/en/gps/view/24h_2019');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
