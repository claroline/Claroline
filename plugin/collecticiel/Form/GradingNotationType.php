<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GradingNotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('notationName', 'text',
                [
                    'required' => true,
                    'label' => false,
                    'attr' => ['class' => 'form-control-notation'],
                ]
            )
            ->add('id', 'hidden')
            ;
    }

    public function getName()
    {
        return 'innova_collecticiel_notation_input_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
                [
                    'language' => 'fr',
                    'data_class' => 'Innova\CollecticielBundle\Entity\GradingNotation',
                    'cascade_validation' => true,
                    'translation_domain' => 'innova_collecticiel',
                ]
        );
    }
}
