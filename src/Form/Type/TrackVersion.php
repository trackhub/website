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
                    'Easiest' => \App\Type\DifficultyType::ENUM_EASIEST,
                    'Easy' => \App\Type\DifficultyType::ENUM_EASY,
                    'More Difficult' => \App\Type\DifficultyType::ENUM_MORE_DIFFICULT,
                    'Very Difficult' => \App\Type\DifficultyType::ENUM_VERY_DIFFICULT,
                    'Extremely Difficult' => \App\Type\DifficultyType::ENUM_EXTREMELY_DIFFICULT,
                ]
            ]
        );
    }
}
