<?php

namespace App\Tests\test\Translations;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class TranslationsTest extends TestCase
{

    private const FILES = [
        'messages.bg.yaml',
        'messages.en.yaml',
        'validators.bg.yaml'
    ];

    /**
     * For readability, translation messages should be sorted alphabetically.
     * Sort the existing list by keys and compare it with the original.
     */
    public function testArrangement(): void
    {
        $parser = new YamlFileLoader();


        foreach (self::FILES as $file) {
            [$domain, $locale] = explode('.', $file);

            $trans = $parser->load(__DIR__ . '/../../../../translations/' . $file, $locale)->all($domain);

            $sorted = $trans;
            ksort($sorted);
            $this->assertSame($sorted, $trans, "The file '" . $file . "' is not sorted alphabetically");
        }
    }
}
