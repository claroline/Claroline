<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class HintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'value', 'textarea', array(
                    'label' => 'Hint.value'
                )
            )
            ->add(
                'penalty', 'text', array(
                    'label' => 'Hint.penalty',
                    'attr' => array('style' => 'width:50px; text-align: end;')
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Hint',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_hinttype';
    }

}
