<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProposalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'ordre', 'text'
            )
            ->add(
                'value', 'textarea', array(
                    'required' => true,
                    'label' => ' ',
                    'attr' => array(
                        'class'=>'form-control',
                        'style' => 'height:34px;',
                        'placeholder' => 'expected_answer'
                    )
                )
            )
            ->add(
                'positionForce', 'checkbox', array(
                    'required' => false, 'label' => ' '
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Proposal',
            ));
    }

    public function getName()
    {
        return 'ujm_exobundle_proposaltype';
    }
}
