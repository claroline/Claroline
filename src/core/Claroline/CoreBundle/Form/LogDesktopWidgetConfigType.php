<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LogDesktopWidgetConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $workspaces = $options['workspaces'];

        if (is_array($workspaces)) {
            foreach ($workspaces as $workspace) {
                $builder->add($workspace->getId(), 'checkbox', array('required' => false, 'label' => $workspace->getName()));
            }
        }
        $builder->add('amount', 'choice', array(
                'choices' => array(
                    '1' => '1',
                    '5' => '5',
                    '10' => '10',
                    '15' => '15',
                    '20' => '20'
                ),
                'required' => true
            )
        );
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