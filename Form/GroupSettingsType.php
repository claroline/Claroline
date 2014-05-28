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

    public function __construct($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $isAdmin = $this->isAdmin;
        $builder->add(
            'platformRole', 'entity', array(
                'class' => 'Claroline\CoreBundle\Entity\Role',
                'expanded' => false,
                'multiple' => false,
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
