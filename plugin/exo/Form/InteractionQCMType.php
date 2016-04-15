<?php

namespace UJM\ExoBundle\Form;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Claroline\CoreBundle\Entity\User;

class InteractionQCMType extends AbstractType
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
            ->add(
                'question', new QuestionType(
                    $this->user, $this->catID
                )
            );
        $builder
            ->add(
                'shuffle', 'checkbox', array(
                    'label' => 'qcm_shuffle',
                    'required' => false,
                    'translation_domain' => 'ujm_exo',
                )
            );
        $builder
            ->add(
                'scoreRightResponse', 'text', array(
                    'required' => false,
                    'label' => 'score_right_label',
                    'attr' => array('placeholder' => 'right_response'),
                    'translation_domain' => 'ujm_exo',
                )
            );
        $builder
            ->add(
                'scoreFalseResponse', 'text', array(
                    'required' => false,
                    'label' => 'score_false_label',
                    'attr' => array('placeholder' => 'false_response', 'class' => 'col-md-2'),
                    'translation_domain' => 'ujm_exo',
                )
            );
        $builder
            ->add(
                'weightResponse', 'checkbox', array(
                    'required' => false,
                    'label' => 'weight_choice',
                    'translation_domain' => 'ujm_exo',
                )
            );
        $builder
            ->add(
                'typeQCM', 'entity', array(
                    'class' => 'UJMExoBundle:TypeQCM',
                    'label' => 'type_qcm',
                    'multiple' => false,
                    'expanded' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('TypeQCM')
                        ->orderBy('TypeQCM.value', 'DESC');
                    },
                    'translation_domain' => 'ujm_exo',
                )
            );
        $builder
            ->add(
                'choices', 'collection', array(
                    'type' => new ChoiceType(),
                    'prototype' => true,
                    'allow_add' => true,
                    'allow_delete' => true,
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'UJM\ExoBundle\Entity\InteractionQCM',
                'cascade_validation' => true,
                'translation_domain' => 'ujm_exo',
            )
        );
    }

    public function getName()
    {
        return 'ujm_exobundle_interactionqcmtype';
    }
}
