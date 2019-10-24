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

    public function load(string $file): array
    {
        $translations = require 'translations/' . $file;
        /**
         * $duplicates variable is loaded from the translation file
         * @var array $duplicates
         */
        $allowed = isset($duplicates) ? $duplicates : [];

        return [
            'translations' => $translations,
            'allowed' => $allowed,
        ];
    }

    /**
     * For readability, translation messages should be sorted alphabetically.
     * Sort the existing list by keys and compare it with the original.
     */
    public function testArrangement(): void
    {
        foreach (self::FILES as $file) {
            $trans = $this->load($file);

            $sorted = $trans['translations'];
            ksort($sorted);
            $this->assertSame($sorted, $trans['translations'], "The file '" . $file . "' is not sorted alphabetically");
        }
    }

    /**
     * Test for duplicate keys
     */
    public function testDuplicateKeys(): void
    {
        foreach (self::FILES as $file) {
            $trans = $this->load($file);

            $output = shell_exec("cat translations/" . $file . " | grep '=>' | awk -F'=>' '{print $1}' | uniq -d | xargs | tr -d '\\n'");
            $this->assertNull($output, "There is duplicate keys in '" . $file . "'");
        }
    }
}
