<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Form\GroupType;

class GroupSettingsType extends GroupType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
            'ownedRoles',
            'entity',
            array(
                'class' => 'Claroline\CoreBundle\Entity\Role', 'expanded' => false,
                'multiple' => true, 'property' => 'name', 'read_only' => false,
                'required' => false
            )
        );
    }

    public function getName()
    {
        return 'group_form';
    }
}