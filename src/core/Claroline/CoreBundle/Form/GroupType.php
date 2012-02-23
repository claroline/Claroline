<?php
namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\WorkspaceRole;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
         $builder->add('name', 'text');
    }
    
    public function getName()
    {
         return 'group_form';
    }
}

