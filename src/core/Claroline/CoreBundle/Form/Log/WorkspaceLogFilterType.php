<?php

namespace Claroline\CoreBundle\Form\Log;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceLogFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'action', 'twolevelselect', array(
                    'label' => 'Show actions for',
                    'translation_domain' => 'log',
                    'attr' => array('class' => 'input-medium'),
                    'choices' => array(
                        'all' => 'all',
                        'workspace-tool-read' => 'log_workspace_read',
                        'resource' => array(
                            'resource-all' => 'all',
                            'resource-create' => 'log_create',
                            'resource-read' => 'log_read',
                            'resource-export' => 'log_export',
                            'resource-delete' => 'log_delete',
                            'resource-update' => 'log_update',
                            'resource-move' => 'log_move',
                            'resource-copy' => 'log_copy',
                            'resource-shortcut' => 'log_shortcut'
                        ),
                        'role' => array(
                            'workspace-role-all' => 'all',
                            'workspace-role-create' => 'log_create',
                            'workspace-role-delete' => 'log_delete',
                            'workspace-role-update' => 'log_update',
                            'workspace-role-change_right' => 'log_change_right',
                            'workspace-role-subscribe_user' => 'log_subscribe_user',
                            'workspace-role-unsubscribe_user' => 'log_unsubscribe_user',
                            'workspace-role-subscribe_group' => 'log_subscribe_group',
                            'workspace-role-unsubscribe_group' => 'log_unsubscribe_group'
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
                'translation_domain' => 'log'
            )
        );
    }
}
