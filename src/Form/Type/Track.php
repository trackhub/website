<?php


namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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

        if ($track == null || $track->getId() == null) {
            $builder->add('file', FileType::class, ['mapped' => false]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }
}
