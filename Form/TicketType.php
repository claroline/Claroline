<?php

namespace FormaLibre\SupportBundle\Form;

use Doctrine\ORM\EntityRepository;
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
                'required' => true,
                'label' => 'description',
                'translation_domain' => 'platform'
            )
        );
        $builder->add(
            'type',
            'entity',
            array(
                'label' => 'type',
                'class' => 'FormaLibreSupportBundle:Type',
                'translation_domain' => 'support',
                'choice_translation_domain' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                },
                'property' => 'formName',
                'expanded' => false,
                'multiple' => false,
                'required' => true
            )
        );
        $builder->add(
            'contactMail',
            'email',
            array(
                'required' => true,
                'label' => 'contact_email'
            )
        );
        $builder->add(
            'contactPhone',
            'text',
            array(
                'required' => true,
                'label' => 'contact_phone'
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
