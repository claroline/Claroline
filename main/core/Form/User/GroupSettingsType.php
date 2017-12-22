<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\User;

use Claroline\CoreBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GroupSettingsType extends GroupType
{
    public function __construct($roles = null, $isAdmin = true, $ngAlias = 'cgfm')
    {
        parent::__construct();
        $this->isAdmin = $isAdmin;
        $this->ngAlias = $ngAlias;
        $this->roles = $roles ? $roles : new ArrayCollection();
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $isAdmin = true;
        $builder->add(
            'platformRoles',
            'entity',
            [
                'label' => 'roles',
                'class' => 'Claroline\CoreBundle\Entity\Role',
                'choice_translation_domain' => true,
                'mapped' => false,
                'data' => $this->roles,
                'expanded' => true,
                'multiple' => true,
                'property' => 'translationKey',
                'disabled' => false,
                'query_builder' => function (EntityRepository $er) use ($isAdmin) {
                    $query = $er->createQueryBuilder('r')
                        ->where('r.type = '.Role::PLATFORM_ROLE)
                        ->andWhere("r.name != 'ROLE_ANONYMOUS'")
                        ->andWhere("r.name != 'ROLE_USER'");

                    if (!$isAdmin) {
                        $query->andWhere("r.name != 'ROLE_ADMIN'");
                    }

                    return $query;
                },
            ]
        );

        $builder->add(
            'organizations',
            'organization_picker',
            [
               'label' => 'organizations',
            ]
        );
    }

    public function getName()
    {
        return 'group_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = ['translation_domain' => 'platform'];
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }
        $default['ng-model'] = 'group';
        $default['ng-controllerAs'] = $this->ngAlias;

        $resolver->setDefaults($default);
    }
}
