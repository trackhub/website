<?php


namespace App\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DifficultyType extends Type
{
    const ENUM_DIFFICULTY = 'enum_difficulty';

    const ENUM_WHITE = 'white';
    const ENUM_GREEN = 'green';
    const ENUM_BLUE = 'blue';
    const ENUM_BLACK = 'black';
    const ENUM_DOUBLE_BLACK = 'double-black';

    const ENUM_VALUES = [
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
