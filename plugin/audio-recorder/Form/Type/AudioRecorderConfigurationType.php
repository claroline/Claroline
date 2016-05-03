<?php

namespace Innova\AudioRecorderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AudioRecorderConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('max_try', 'integer', array('required' => true, 'attr' => array('min' => 1, 'max' => 5)))
            ->add('max_recording_time', 'integer', array('required' => true, 'attr' => array('min' => 0)))
            ->add('', 'submit', array('label' => 'submit_config_label', 'translation_domain' => 'tools', 'attr' => array('class' => 'btn btn-primary pull-right')));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());

        return $this;
    }

    public function getDefaultOptions()
    {
        return array(
          'data_class' => 'Innova\AudioRecorderBundle\Entity\AudioRecorderConfiguration',
          'translation_domain' => 'tools',
      );
    }

    public function getName()
    {
        return 'audio_recorder_configuration';
    }
}
