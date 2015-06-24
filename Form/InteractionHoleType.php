<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Claroline\CoreBundle\Entity\User;

class InteractionHoleType extends AbstractType
{

    private $user;
    private $catID;

    public function __construct(User $user, $catID = -1)
    {
        $this->user = $user;
        $this->catID = $catID;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('interaction', new InteractionType(
                    $this->user,
                    $this->catID
                    )
            )
            ->add('html','tinymce', array(
                    'attr' => array('data-new-tab' => 'yes'),
                    'label' => 'hole',
                    'attr' => array('data-before-unload' => 'off'),
                    'required' => false
                )
            )
            ->add('holes', 'collection', array('type' => new HoleType,
                                               'prototype' => true,
                                               //'by_reference' => false,
                                               'allow_add' => true,
                                               'allow_delete' => true));
            //->add('interaction')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\InteractionHole',
                'cascade_validation' => true
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_interactionholetype';
    }
}
