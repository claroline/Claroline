<?php

namespace FormaLibre\SupportBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TicketInterventionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'type',
            'entity',
            [
                'label' => 'type',
                'class' => 'FormaLibreSupportBundle:Type',
                'translation_domain' => 'support',
                'choice_translation_domain' => true,
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]
        );
        $builder->add(
            'status',
            'entity',
            [
                'label' => 'status',
                'class' => 'FormaLibreSupportBundle:Status',
                'translation_domain' => 'support',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')->orderBy('s.order', 'ASC');
                },
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]
        );
        $builder->add(
            'privateComment',
            'tinymce',
            [
                'label' => 'internal_note',
                'translation_domain' => 'support',
                'required' => false,
                'mapped' => false,
            ]
        );
        $builder->add(
            'publicComment',
            'tinymce',
            [
                'label' => 'message',
                'translation_domain' => 'platform',
                'required' => false,
                'mapped' => false,
            ]
        );
    }

    public function getName()
    {
        return 'ticket_intervention_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'support']);
    }
}
