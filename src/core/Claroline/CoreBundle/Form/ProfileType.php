<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Entity\Role;

class ProfileType extends BaseProfileType
{
    private $grantRole;

    public function __construct($platformRoles)
    {
        foreach ($platformRoles as $role) {

            if ($role->getTranslationKey() == 'admin') {
                $this->grantRole = true;
            }
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('administrativeCode', 'text', array('required' => false))
            ->add('plainPassword', 'repeated', array('type' => 'password'))
            ->add('mail', 'email', array('required' => false))
            ->add('phone', 'text', array('required' => false));
        if ($this->grantRole == true) {
            $builder->add(
                'platformRole',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => false,
                    'property' => 'translationKey',
                    'disabled' => false,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.roleType != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            );
        } else {
            $builder->add(
                'platformRole',
                'entity',
                array(
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => false,
                    'property' => 'translationKey',
                    'disabled' => true,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.roleType != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            );
        }
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'platform'
        );
    }
}