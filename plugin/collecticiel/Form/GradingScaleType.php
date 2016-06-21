<?php

namespace Innova\CollecticielBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GradingScaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('scaleName', 'text',
                array(
                    'required' => true,
                    'label' => false,
                    'attr' => array('class' => 'form-control-scale'),
                   )
                )
            ->add('id', 'hidden')
            ;
    }

    public function getName()
    {
        return 'innova_collecticiel_appreciation_input_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
                array(
                    'language' => 'fr',
                    'data_class' => 'Innova\CollecticielBundle\Entity\GradingScale',
                    'cascade_validation' => true,
                    'translation_domain' => 'innova_collecticiel',
                    )
        );
    }
}
