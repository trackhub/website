<?php


namespace App\Form\Type;

use App\Entity\Video\Youtube;
use App\Form\Type\Track\Video\YoutubeType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            'name',
            TextType::class,
            [
                'required' => false,
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
                    'mapped' => false,
                    'label' => '.gpx file'
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
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }
}
