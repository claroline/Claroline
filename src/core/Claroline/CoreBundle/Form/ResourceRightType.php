<?php

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResourceRightType extends AbstractType
{
    private $hasRecustiveOption;

    public function __construct(AbstractResource $resource)
    {
        $this->hasRecustiveOption = false;
        if ($resource instanceof \Claroline\CoreBundle\Entity\Resource\Directory) {
            $this->hasRecustiveOption = true;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('canOpen', 'checkbox');
        $builder->add('canEdit', 'checkbox');
        $builder->add('canDelete', 'checkbox');
        $builder->add('canCopy', 'checkbox');
        $builder->add('canExport', 'checkbox');

        if ($this->hasRecustiveOption) {
            $builder->add('isRecursive', 'checkbox', array('mapped' => false));
        } else {
            $builder->add('isRecursive', 'hidden', array('data' => false, 'mapped' => false));
        }
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