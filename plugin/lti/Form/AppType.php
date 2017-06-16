<?php

namespace UJM\LtiBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AppType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title', 'text', [
                    'label' => ' ',
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'title',
                    ],
                ]
            )
            ->add(
                'url', 'text', [
                    'label' => ' ',
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'url',
                    ],
                ]
            )
            ->add(
                'appkey', 'text', [
                    'label' => ' ',
                    'required' => false,
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'key',
                    ],
                ]
            )
            ->add(
                'secret', 'text', [
                    'label' => ' ',
                    'required' => false,
                    'attr' => ['style' => 'height:34px; ',
                        'class' => 'form-control',
                        'placeholder' => 'secret',
                    ],
                ]
            )
            ->add(
                'description', 'textarea', [
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'lti']);
    }
}
