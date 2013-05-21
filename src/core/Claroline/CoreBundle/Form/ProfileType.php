<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;


class ProfileType extends BaseProfileType
{
    private $grantRole;

    public function __construct($platformRoles)
    {
        $this->platformRoles = new ArrayCollection($platformRoles);

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
                'platformRoles',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->platformRoles,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'disabled' => false,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            );
        } else {
            $builder->add(
                'platformRoles',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->platformRoles,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'disabled' => true,
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type != " . Role::WS_ROLE)
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