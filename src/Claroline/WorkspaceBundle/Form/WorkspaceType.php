<?php

namespace Claroline\WorkspaceBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class WorkspaceType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name');
    }

    public function getDefaultOptions(array $options)
    {
        return array(
        'data_class' => 'Claroline\WorkspaceBundle\Entity\Workspace',
        );
    }
}