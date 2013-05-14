<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Validator\Constraints\AdminWorkspaceTagUniqueName;

class AdminLogFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'action', 'twolevelselect', array(
                    'label' => 'Show actions for',
                    'attr' => array('class' => 'input-medium'),
                    'choices' => array(
                        'all' => 'all',
                        'group' => array(
                            "'group_all'" => 'all',
                            "'group_create'" => 'log_create',
                            "'group_delete'" => 'log_delete',
                            "'group_update'" => 'log_update',
                            "'group_add_user'" => 'log_add_user',
                            "'group_remove_user'" => 'log_remove_user'
                        ),
                        'user' => array(
                            "'user_all'" => 'all',
                            "'user_create'" => 'log_create',
                            "'user_delete'" => 'log_delete',
                            "'user_update'" => 'log_update',
                            "'user_login'" => 'log_login'
                        ),
                        'workspace' => array(
                            "'workspace_all'" => 'all',
                            "'workspace_create'" => 'log_create',
                            "'workspace_delete'" => 'log_delete',
                            "'workspace_update'" => 'log_update'
                        )
                    )
                )
            )
            ->add(
                'range',
                'daterange',
                array(
                    'label' => 'for period',
                    'required' => false,
                    'attr' => array('class' => 'input-medium')
                )
            )
            ->add(
                'user',
                'simpleautocomplete',
                array(
                    'label' => 'for user',
                    'entity_reference' => 'user',
                    'required' => false,
                    'attr' => array('class' => 'input-medium')
                )
            );
    }

    public function getName()
    {
        return 'admin_log_filter_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
        ->setDefaults(
            array(
                'translation_domain' => 'platform'
            )
        );
    }
}