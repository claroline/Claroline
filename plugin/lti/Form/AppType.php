<?php

namespace UJM\LtiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title', TextType::class, [
                    'label' => ' ',
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'title',
                    ],
                ]
            )
            ->add(
                'url', UrlType::class, [
                    'label' => ' ',
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => UrlType::class,
                    ],
                ]
            )
            ->add(
                'appkey', TextType::class, [
                    'label' => ' ',
                    'required' => false,
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'key',
                    ],
                ]
            )
            ->add(
                'secret', TextType::class, [
                    'label' => ' ',
                    'required' => false,
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'secret',
                    ],
                ]
            )
            ->add(
                'description', TextareaType::class, [
                    'label' => ' ',
                    'required' => false,
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'description',
                    ],
                ]
            );
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'lti']);
    }
}
