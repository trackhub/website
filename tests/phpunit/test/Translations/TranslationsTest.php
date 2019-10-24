<?php

namespace App\Tests\test\Translations;

use PHPUnit\Framework\TestCase;

class TranslationsTest extends TestCase
{

    private const FILES = [
        'messages.bg.php',
        'messages.en.php',
        'validators.bg.php'
    ];

    /**
     * For readability, translation messages should be sorted alphabetically.
     * Sort the existing list by keys and compare it with the original.
     */
    public function testArrangement(): void
    {
        foreach (self::FILES as $file) {
            $trans = require 'translations/' . $file;

            $sorted = $trans;
            ksort($sorted);
            $this->assertSame($sorted, $trans, "The file '" . $file . "' is not sorted alphabetically");
        }
    }
}
