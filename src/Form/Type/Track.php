<?php

namespace App\Form\Type;

use App\Form\Type\Track\Video\YoutubeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;

class Track extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $track = null;
        if (isset($options['data'])) {
            $track = $options['data'];
        }

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
                    'cycling' => \App\Entity\Track::TYPE_CYCLING,
                    'hiking' => \App\Entity\Track::TYPE_HIKING,
                ],
            ]
        );

        $builder->add(
            'visibility',
            ChoiceType::class,
            [
                'choices' => [
                    'public' => \App\Entity\Track::VISIBILITY_PUBLIC,
                    'unlisted' => \App\Entity\Track::VISIBILITY_UNLISTED,
                ],
            ]
        );

        if ($track == null || $track->getId() == null) {
            $builder->add(
                'file',
                FileType::class,
                [
                    'label' => 'Track file. Supported formats: .gpx',
                ]
            );
        }

        $builder->add(
            'videosYoutube',
            CollectionType::class,
            [
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => true,
                'entry_type' => YoutubeType::class,
            ]
        );

        $builder->add(
            'slug',
            TextType::class,
            [
                'required' => false,
                'label' => 'Short link',
                'constraints' => new Regex([
                    'pattern' => '/^[a-zA-Z0-9_\-]+$/',
                    'message' => 'Should contains only letters, numbers, dash and underscore',
                ]),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }
}
