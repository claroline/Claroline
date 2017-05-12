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

use Claroline\CoreBundle\Entity\Role;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileCreationType extends AbstractType
{
    private $platformRoles;
    private $langs;
    private $isAdmin;
    private $authenticationDrivers;
    private $localeMnanager;
    private $currentUser;

    /**
     * Constructor.
     *
     * @param Role[] $platformRoles
     * @param array  $langs
     * @param bool   $isAdmin
     */
    public function __construct(
        $localeManager,
        array $platformRoles,
        $currentUser,
        $authenticationDrivers = null
    ) {
        $this->platformRoles = $platformRoles;
        $this->langs = $localeManager->retrieveAvailableLocales();
        $this->currentUser = $currentUser;
        $this->isAdmin = in_array('ROLE_ADMIN', $currentUser->getRoles());
        $this->authenticationDrivers = $authenticationDrivers;
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (php_sapi_name() === 'cli') {
            $this->isAdmin = true;
        }

        parent::buildForm($builder, $options);
        $isAdmin = $this->isAdmin;

        $builder->add('firstName', 'text', ['label' => 'first_name'])
            ->add('lastName', 'text', ['label' => 'last_name'])
            ->add('username', 'text', ['label' => 'username'])
            ->add(
                'plainPassword',
                'repeated',
                [
                    'type' => 'password',
                    'first_options' => ['label' => 'password'],
                    'second_options' => ['label' => 'verification'],
                ]
            )
            ->add(
                'administrativeCode',
                'text',
                [
                    'required' => false, 'label' => 'administrative_code',
                ]
            )
            ->add('mail', 'email', ['required' => true, 'label' => 'email'])
            ->add('phone', 'text', ['required' => false, 'label' => 'phone'])
            ->add('locale', 'choice', ['choices' => $this->langs, 'required' => false, 'label' => 'language'])
            ->add(
                'authentication',
                'choice',
                [
                    'choices' => $this->authenticationDrivers,
                    'required' => false,
                    'label' => 'authentication',
                ]
            )
            ->add(
                'platformRoles',
                'entity',
                [
                    'label' => 'roles',
                    'choice_translation_domain' => true,
                    'mapped' => false,
                    'data' => $this->platformRoles,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'query_builder' => function (EntityRepository $er) use ($isAdmin) {
                        $query = $er->createQueryBuilder('r')
                                ->where('r.type = '.Role::PLATFORM_ROLE)
                                ->andWhere("r.name != 'ROLE_USER'")
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                        if (!$isAdmin) {
                            $query->andWhere("r.name != 'ROLE_ADMIN'");
                        }

                        return $query;
                    },
                ]
            );

        $currentUser = $this->currentUser;

        $builder->add(
                'organizations',
                'entity',
                [
                    'label' => 'organizations',
                    'class' => 'Claroline\CoreBundle\Entity\Organization\Organization',
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'name',
                    'query_builder' => function (EntityRepository $er) use ($currentUser, $isAdmin) {
                        $query = $er->createQueryBuilder('o');
                        if (!$isAdmin) {
                            $query->leftJoin('o.administrators', 'oa')
                            ->where('oa.id = :id')
                            ->orWhere('o.default = true')
                            ->setParameter('id', $currentUser->getId());
                        }

                        return $query;
                    },
                ]
            )
            ->add(
                'groups',
                'entity',
                [
                    'label' => 'groups',
                    'class' => 'Claroline\CoreBundle\Entity\Group',
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'name',
                ]
            );
    }

    public function getName()
    {
        return 'profile_form_creation';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = [
            'data_class' => 'Claroline\CoreBundle\Entity\User',
            'validation_groups' => ['registration', 'Default'],
            'translation_domain' => 'platform',
        ];
        if ($this->forApi) {
            $default['csrf_protection'] = false;
        }

        $resolver->setDefaults($default);
    }

    public function enableApi()
    {
        $this->forApi = true;
    }
}
