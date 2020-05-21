<?php

namespace App\Tests\Html;

use App\Html\Purifier;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PurifierTest extends KernelTestCase
{
    private Purifier $purifier;

    public function setUp(): void
    {
        static::bootKernel();
        $this->purifier = self::$container->get(Purifier::class);
    }

    public function xssCases()
    {
        return [
            ['<a href="javascript:alert(1)">test</a>', 'alert'],
            ['<a href="http://localhost" onclick="alert(1)">test</a>', 'alert'],
            ['<a href="http://localhost" onmouseover="alert(1)">test</a>', 'alert'],
            ['<img src="1"onerror=alert(1)">', 'alert'], // this is intentional invalid html
        ];
    }

    /**
     * @dataProvider xssCases
     */
    public function testXss(string $html, string $xss)
    {
        $filtered = $this->purifier->purify($html);
        $this->assertStringNotContainsString($xss, $filtered);
    }

    public function validHtml()
    {
        return [
            ['<a href="//track-hub.com">test</a>'],
            ['<em><strong>iiiiii</strong></em>'],
        ];
    }

    /**
     * @dataProvider validHtml
     */
    public function testValidHtml($html)
    {
        $filtered = $this->purifier->purify($html);
        $this->assertSame($html, $filtered);
    }
}
