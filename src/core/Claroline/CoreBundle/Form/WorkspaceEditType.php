<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class WorkspaceEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array('required' => true));
        $builder->add('code', 'text', array('required' => true));
        $builder->add('displayable', 'checkbox', array('required' => false));
        $builder->add('selfRegistration', 'checkbox', array('required' => false));
        $builder->add('selfUnregistration', 'checkbox', array('required' => false));
    }

    public function getName()
    {
        return 'workspace_edit_form';
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
