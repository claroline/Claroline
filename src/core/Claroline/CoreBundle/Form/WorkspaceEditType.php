<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class WorkspaceEditType extends WorkspaceType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->remove('type');
    }
    
    public function getName()
    {
        return 'workspace_edit_form';
    }
}