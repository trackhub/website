<?php


namespace App\Form\Type\Track;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;


class Description extends AbstractType
{
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'language',
            EntityType::class,
            [
                'class' => Language::class,
                'choice_label' => 'name',
                'choices' => $this->languageRepository->findAllNames(),
                'placeholder' => 'Choose a language',
                'required' => true,
            ]
        );

        $builder->add(
            'description',
            TextType::class,
            [
                'attr' => [
                    'placeholder' => 'Track description'
                ],
                'required' => false,

            ]
        );
    }

}