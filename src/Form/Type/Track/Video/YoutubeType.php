<?php

namespace App\Form\Type\Track\Video;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class YoutubeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'link',
            TextType::class,
            [
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://www.youtube.com/watch?v=EqYgAX6D43Q',
                ],
            ]
        );
    }
}
