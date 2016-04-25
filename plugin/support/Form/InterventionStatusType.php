<?php

namespace FormaLibre\SupportBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InterventionStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'status',
            'entity',
            array(
                'label' => 'status',
                'class' => 'FormaLibreSupportBundle:Status',
                'translation_domain' => 'support',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->where('s.type = 0')
                        ->orderBy('s.order', 'ASC');
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            )
        );
        $builder->add(
            'externalComment',
            'tinymce',
            array(
                'required' => true,
                'label' => 'client_message',
            )
        );
        $builder->add(
            'internalComment',
            'tinymce',
            array(
                'required' => true,
                'label' => 'internal_message',
            )
        );
    }

    public function getName()
    {
        return 'intervention_status_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'support'));
    }
}
