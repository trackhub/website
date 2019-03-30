<?php


namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class Track extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => [
                    'cycling' => \App\Entity\Track::TYPE_CYCLING,
                    'hiking' => \App\Entity\Track::TYPE_HIKING,
                ],
            ]
        );
        $builder->add('file', FileType::class);
    }
}
