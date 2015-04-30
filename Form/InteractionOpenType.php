<?php

namespace UJM\ExoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Claroline\CoreBundle\Entity\User;

class InteractionOpenType extends AbstractType
{
    private $user;
    private $catID;

    public function __construct(User $user, $catID = -1)
    {
        $this->user  = $user;
        $this->catID = $catID;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'interaction', new InteractionType(
                    $this->user, $this->catID
                )
            );
        $builder
            ->add(
                'typeopenquestion', 'entity', array(
                    'class' => 'UJM\\ExoBundle\\Entity\\TypeOpenQuestion',
                    'label' => 'TypeOpenQuestion.value'
                )
            );
        $builder
            ->add(
                'orthographyCorrect', 'checkbox', array(
                    'label' => 'Inter_Hole.orthography',
                    'required' => false
                )
            );
        /*$builder
            ->add(
                'unit', 'entity', array(
                    'class' => 'UJM\\ExoBundle\\Entity\\Unit',
                    'label' => 'Unit.value',
                    'required' => false
                )
            );*/
        $builder
            ->add(
                'wordResponses', 'collection', array(
                    'type' => new WordResponseType,
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true
                )
            );
        $builder
            ->add(
                'scoreMaxLongResp', 'text', array(
                'required' => false,
                'label' => 'Inter_Open.ScoreMaxLongResp'
                )
            );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\InteractionOpen',
                'cascade_validation' => true
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_interactionopentype';
    }
}