<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
                'choices' => [
                    'form.place.type.generic' => \App\Entity\Place::TYPE_GENERIC,
                    'form.place.type.drinking_water' => \App\Entity\Place::TYPE_DRINKING_FOUNTAIN,
                ],
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
    }
}
