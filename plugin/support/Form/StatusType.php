<?php

namespace FormaLibre\SupportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class StatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array(
                'required' => true,
                'label' => 'name',
                'translation_domain' => 'platform',
            )
        );
        $builder->add(
            'code',
            'text',
            array(
                'required' => true,
                'label' => 'code',
                'translation_domain' => 'platform',
            )
        );
        $builder->add(
            'type',
            'choice',
            array(
                'label' => 'type',
                'choices' => array(0, 1, 2, 3, 4),
                'expanded' => false,
                'multiple' => false,
                'required' => true,
                'translation_domain' => 'platform',
            )
        );
    }

    public function getName()
    {
        return 'status_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'support'));
    }
}
