<?php


namespace App\Type;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DifficultyType extends Type
{
    const ENUM_DIFFICULTY = 'enum_difficulty';

    const ENUM_EASIEST = 'easiest';
    const ENUM_EASY = 'easy';
    const ENUM_MORE_DIFFICULT = 'more-difficult';
    const ENUM_VERY_DIFFICULT = 'very-difficult';
    const ENUM_EXTREMELY_DIFFICULT = 'extremely-difficult';

    const ENUM_VALUES = [
        self::ENUM_EASIEST,
        self::ENUM_EASY,
        self::ENUM_MORE_DIFFICULT,
        self::ENUM_VERY_DIFFICULT,
        self::ENUM_EXTREMELY_DIFFICULT
    ];

    public function getName()
    {
        return self::ENUM_DIFFICULTY;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return "ENUM('easiest', 'easy', 'more-difficult', 'very-difficult', 'extremely-difficult')";
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return True;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!in_array($value, self::ENUM_VALUES)) {
            throw new \InvalidArgumentException("Invalid difficulty: " . $value);
        }

        return $value;
    }
}