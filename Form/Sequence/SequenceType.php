<?php

namespace UJM\ExoBundle\Form\Sequence;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SequenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name', 'text'
            )
            ->add('description', 'tinymce', array(
                    'attr' => array('data-new-tab' => 'yes'),
                    'label' => 'Description', 'required' => false,
                )
            )
            ->add('startDate', 'datetime', array(
                    'data' => new \DateTime(),
                    'attr' => array('style' => 'display:none;'),
                    'widget' => 'single_text',
                    'label' => ' ',
                    'input' => 'datetime',
                )
            )
            ->add('endDate', 'datetime', array(
                    'data' => null,
                    'attr' => array('style' => 'display:none;'),
                    'label' => ' ',
                    'widget' => 'single_text',
                    'required' => false,
                    'input' => 'datetime',
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\Sequence\Sequence',
                'translation_domain' => 'ujm_sequence',
            )
        );
    }

    public function getName()
    {
        return 'sequence_type';
    }
}
