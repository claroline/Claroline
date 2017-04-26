<?php

namespace UJM\LtiBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LtiResourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('LtiApp', 'entity', [
            'class' => 'UJMLtiBundle:LtiApp',
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('l')
                    ->orderBy('l.title', 'ASC');
            },
            'choice_label' => 'title',
            'label' => 'choice_app',
        ]);
        $builder->add(
            'name',
            'text',
            [
                'label' => 'name_app',
                'attr' => ['autofocus' => true],
            ]
        );
        $builder->add(
            'openInNewTab',
            'checkbox',
            [
                'label' => 'open_application_in_a_new_window',
            ]

        );
    }

    public function getName()
    {
        return 'ltiApp_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'lti']);
    }
}
