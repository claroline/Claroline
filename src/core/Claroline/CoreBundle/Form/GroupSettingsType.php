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
                'class' => 'Claroline\CoreBundle\Entity\Role',
                'expanded' => false,
                'multiple' => true,
                'property' => 'translationKey',
                'disabled' => false,
                'query_builder' => function(\Doctrine\ORM\EntityRepository $er){
                    return $er->createQueryBuilder('r')
                        ->add('where', 'r NOT INSTANCE OF Claroline\CoreBundle\Entity\WorkspaceRole');
                }
            )
        );
    }

    public function getName()
    {
        return 'group_form';
    }

    public function getDefaultOptions(array $options)
    {
       return array(
           'translation_domain' => 'platform'
       );
    }
}