<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Validator\Constraints\AdminWorkspaceTagUniqueName;

class WorkspaceLogFilterType extends AbstractType
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
                        "'ws_tool_read'" => 'log_workspace_read',
                        'resource' => array(
                            "'resource_all'" => 'all',
                            "'resource_create'" => 'log_create',
                            "'resource_read'" => 'log_read',
                            "'resource_export'" => 'log_export',
                            "'resource_delete'" => 'log_delete',
                            "'resource_update'" => 'log_update',
                            "'resource_move'" => 'log_move',
                            "'resource_copy'" => 'log_copy',
                            "'resource_shortcut'" => 'log_shortcut',
                            "'resource_child_update'" => 'log_child_update'
                        ),
                        'role' => array(
                            "'ws_role_all'" => 'all',
                            "'ws_role_create'" => 'log_create',
                            "'ws_role_delete'" => 'log_delete',
                            "'ws_role_update'" => 'log_update',
                            "'ws_role_change_right'" => 'log_change_right',
                            "'ws_role_subscribe_user'" => 'log_subscribe_user',
                            "'ws_role_unsubscribe_user'" => 'log_unsubscribe_user',
                            "'ws_role_subscribe_group'" => 'log_subscribe_group',
                            "'ws_role_unsubscribe_group'" => 'log_unsubscribe_group'
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
        return 'workspace_log_filter_form';
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