<?php

namespace Innova\VideoRecorderBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VideoRecorderConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('max_recording_time', 'integer', ['required' => true, 'attr' => ['min' => 0]])
            ->add('validate', 'submit', ['label' => 'validate', 'translation_domain' => 'innova_video_recorder', 'attr' => ['class' => 'btn btn-primary pull-right']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults($this->getDefaultOptions());

        return $this;
    }

    public function getDefaultOptions()
    {
        return [
          'data_class' => 'Innova\VideoRecorderBundle\Entity\VideoRecorderConfiguration',
          'translation_domain' => 'innova_video_recorder',
      ];
    }

    public function getName()
    {
        return 'video_recorder_configuration';
    }
}
