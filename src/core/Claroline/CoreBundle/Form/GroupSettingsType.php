<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Entity\WorkspaceRole;

class GroupSettingsType extends GroupType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
          parent::buildForm($builder, $options);
          $builder->add(
              'ownedRoles', 'entity', array(
                  'class' => 'ClarolineCoreBundle:Role', 'expanded' => false,
                  'multiple' => true, 'property' => 'name', 'read_only' => false,
                  'required' => false)
          );
    }
    
    public function getName()
    {
         return 'group_form';
    }
}


