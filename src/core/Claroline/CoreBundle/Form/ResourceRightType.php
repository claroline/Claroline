<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResourceRightType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('canOpen', 'checkbox');
        $builder->add('canEdit', 'checkbox');
        $builder->add('canDelete', 'checkbox');
        $builder->add('canCopy', 'checkbox');
        $builder->add('canExport', 'checkbox');
    }

    public function getName()
    {
        return 'resources_rights_form';
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