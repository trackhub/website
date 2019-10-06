<?php

namespace App\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DifficultyType extends Type
{
    private const ENUM_DIFFICULTY = 'enum_difficulty';

    public const ENUM_WHITE = 'white';
    public const ENUM_GREEN = 'green';
    public const ENUM_BLUE = 'blue';
    public const ENUM_BLACK = 'black';
    public const ENUM_DOUBLE_BLACK = 'double-black';

    private const ENUM_VALUES = [
        null,
        self::ENUM_WHITE,
        self::ENUM_GREEN,
        self::ENUM_BLUE,
        self::ENUM_BLACK,
        self::ENUM_DOUBLE_BLACK
    ];

    public function getName()
    {
        return self::ENUM_DIFFICULTY;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('white', 'green', 'blue', 'black', 'double-black')";
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, self::ENUM_VALUES)) {
            throw new \InvalidArgumentException("Invalid difficulty: " . $value);
        }

        return $value;
    }
}
