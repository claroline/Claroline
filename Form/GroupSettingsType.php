<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupSettingsType extends GroupType
{
    private $isAdmin;
    private $roles;

    public function __construct($isAdmin, array $roles)
    {
        $this->isAdmin = $isAdmin;
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $isAdmin = $this->isAdmin;
        $builder->add(
            'platformRoles',
            'entity',
            array(
                'label' => 'roles',
                'class' => 'Claroline\CoreBundle\Entity\Role',
                'data' => $this->roles,
                'mapped' => false,
                'expanded' => true,
                'multiple' => true,
                'property' => 'translationKey',
                'disabled' => false,
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($isAdmin){
                    $query = $er->createQueryBuilder('r')
                        ->where("r.type != " . Role::WS_ROLE)
                        ->andWhere("r.name != 'ROLE_ANONYMOUS'");

                    if (!$isAdmin) {
                        $query->andWhere("r.name != 'ROLE_ADMIN'");
                    }

                    return $query;
                }
            )
        );
    }

    public function getName()
    {
        return 'group_form';
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
