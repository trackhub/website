<?php


namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class TrackVersion extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class);

        $builder->add(
            'difficulty',
            ChoiceType::class,
            [
                'choices' => [
                    'White' => \App\Type\DifficultyType::ENUM_WHITE,
                    'Green' => \App\Type\DifficultyType::ENUM_GREEN,
                    'Blue' => \App\Type\DifficultyType::ENUM_BLUE,
                    'Black' => \App\Type\DifficultyType::ENUM_BLACK,
                    'Double Black' => \App\Type\DifficultyType::ENUM_DOUBLE_BLACK,
                ]
            ]
        );
    }
}
