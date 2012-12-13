<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ResourceRightsType extends AbstractType
{
    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
         $builder->add('canSee', 'checkbox');
         $builder->add('canDelete', 'checkbox');
         $builder->add('canOpen', 'checkbox');
         $builder->add('canEdit', 'checkbox');
         $builder->add('canCopy', 'checkbox');
         $builder->add('canShare', 'checkbox');
    }

    public function getName()
    {
        return 'resources_rights_form';
    }

    public function getDefaultOptions(array $options)
    {
       return array(
           'translation_domain' => 'platform'
       );
    }
}

