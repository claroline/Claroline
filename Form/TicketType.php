<?php

namespace FormaLibre\SupportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            'text',
            array(
                'required' => true,
                'label' => 'title',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'description',
            'tinymce',
            array(
                'required' => false,
                'label' => 'description',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'priority',
            'choice',
            array(
                'choices' => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10),
                'label' => 'priority'
            )
        );
    }

    public function getName()
    {
        return 'ticket_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'support'));
    }
}
