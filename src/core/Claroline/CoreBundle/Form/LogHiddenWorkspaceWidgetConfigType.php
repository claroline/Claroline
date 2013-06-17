<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LogHiddenWorkspaceWidgetConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $workspaces = $options['workspaces'];
        foreach ($workspaces as $workspace) {
            $builder->add($workspace->getId(), 'checkbox', array('required' => false, 'label' => $workspace->getName()));
        }
    }

    public function getName()
    {
        return 'log_hidden_workspace_widget_config';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'workspaces' => array(),
            'translation_domain' => 'platform'
        ));
    }
}