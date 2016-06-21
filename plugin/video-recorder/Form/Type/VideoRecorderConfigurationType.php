<?php

namespace Innova\VideoRecorderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoRecorderConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('max_recording_time', 'integer', array('required' => true, 'attr' => array('min' => 0)))
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
          'data_class' => 'Innova\VideoRecorderBundle\Entity\VideoRecorderConfiguration',
          'translation_domain' => 'tools',
      );
    }

    public function getName()
    {
        return 'video_recorder_configuration';
    }
}
