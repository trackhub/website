<?php

namespace App\Form\Type;

use App\Translations;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

class Place extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'nameEn',
            TextType::class,
            [
                'required' => false,
                'label' => 'Name in English',
            ]
        );

        $builder->add(
            'nameBg',
            TextType::class,
            [
                'required' => false,
                'label' => 'Name in Bulgarian',
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => Translations::placeTypeValueTypes(),
            ],
        );

        $coordinatesConstraints = [
            new Range([
                'min' => -180,
                'max' => 180,
            ])
        ];

        $builder->add(
            'lat',
            NumberType::class,
            [
                'scale' => 8,
                'label' => 'Latitude',
                'constraints' => $coordinatesConstraints,
            ]
        );

        $builder->add(
            'lng',
            NumberType::class,
            [
                'scale' => 8,
                'label' => 'Longitude',
                'constraints' => $coordinatesConstraints,
            ]
        );

        $builder->add(
            'descriptionBg',
            TextareaType::class,
            [
                'required' => false,
                'label' => 'Description in Bulgarian',
                'attr' => [
                    'data-html' => 'wysiwyg',
                    'style' => 'min-height: 300px',
                ]
            ]
        );

        $builder->add(
            'descriptionEn',
            TextareaType::class,
            [
                'required' => false,
                'label' => 'Description in English',
                'attr' => [
                    'data-html' => 'wysiwyg',
                    'style' => 'min-height: 300px',
                ]
            ]
        );
    }
}
