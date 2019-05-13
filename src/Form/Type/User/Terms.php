<?php

namespace App\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Required;

class Terms extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'agreeTerms',
                CheckboxType::class,
            [
                'required' => true,
                'label' => 'I agree with the Terms of the Service',
                'constraints' => [
                    new Required(),
                    new IsTrue(),
                ],
            ]
        );

        $builder->add(
            'agreePrivacy',
            CheckboxType::class,
            [
                'required' => true,
                'label' => 'I agree with the Privacy Policy',
                'constraints' => [
                    new Required(),
                    new IsTrue(),
                ],
            ]
        );
    }
}
