<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            array('required' => true)
        );
        $builder->add(
            'description',
            'tinymce',
            array('required' => false)
        );
        $builder->add(
            'defaultResource',
            'resourcePicker',
            array(
                'required' => false,
                'mapped' => false,
                'label' => 'default_resource',
                'attr' => array(
                    'data-restrict-for-owner' => 1,
                ),
            )
        );
        $builder->add(
            'maxUsers',
            IntegerType::class,
            array(
                'attr' => array('min' => 0),
                'required' => false,
            )
        );
        $builder->add(
            'isPublic',
            ChoiceType::class,
            array(
                'choices' => array(
                    true => 'public',
                    false => 'private',
                ),
                'required' => true,
                'attr' => array('class' => 'advanced-param'),
            )
        );
        $builder->add(
            'selfRegistration',
            CheckboxType::class,
            array(
                'required' => true,
                'attr' => array('class' => 'advanced-param'),
            )
        );
        $builder->add(
            'selfUnregistration',
            CheckboxType::class,
            array(
                'required' => true,
                'attr' => array('class' => 'advanced-param'),
            )
        );
        $builder->add(
            'resourceTypes',
            'entity',
            array(
                'required' => false,
                'mapped' => false,
                'expanded' => true,
                'multiple' => true,
                'translation_domain' => 'resource',
                'label' => 'user_creatable_resources',
                'class' => 'ClarolineCoreBundle:Resource\ResourceType',
                'property' => 'name',
                'attr' => array('class' => 'advanced-param'),
            )
        );
    }

    public function getName()
    {
        return 'team_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'team'));
    }
}
