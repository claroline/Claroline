<?php

namespace Innova\AudioRecorderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AudioRecorderConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('max_try', 'integer', [
                      'required' => true,
                      'attr' => [
                          'min' => 1,
                          'max' => 5,
                        ],
                    ])
                ->add('max_recording_time', 'integer', [
                      'required' => true,
                      'attr' => [
                        'min' => 0,
                      ],
                    ])
                ->add('validate', 'submit', [
                  'label' => 'validate',
                  'translation_domain' => 'innova_audio_recorder',
                  'attr' => [
                      'class' => 'btn btn-primary pull-right',
                    ],
                  ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());

        return $this;
    }

    public function getDefaultOptions()
    {
        return [
          'data_class' => 'Innova\AudioRecorderBundle\Entity\AudioRecorderConfiguration',
          'translation_domain' => 'innova_audio_recorder',
      ];
    }

    public function getName()
    {
        return 'audio_recorder_configuration';
    }
}
