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
use Claroline\CoreBundle\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    private $platformRoles;
    private $isAdmin;
    private $isGrantedUserAdministration;
    private $langs;
    private $authenticationDrivers;
    private $accesses;
    private $currentUser;

    /**
     * Constructor.
     *
     * @param Role[]   $platformRoles
     * @param bool     $isAdmin
     * @param string[] $langs
     */
    public function __construct(
        $localeManager,
        array $platformRoles,
        $isAdmin,
        $isGrantedUserAdministration,
        $accesses,
        $authenticationDrivers = null,
        $currentUser = null
    ) {
        $this->accesses = $accesses;
        $this->platformRoles = $platformRoles;
        $this->isAdmin = $isAdmin;
        $this->isGrantedUserAdministration = $isGrantedUserAdministration;
        $this->langs = $localeManager->retrieveAvailableLocales();
        $this->authenticationDrivers = $authenticationDrivers;
        $this->forApi = false;
        $this->currentUser = $currentUser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (php_sapi_name() === 'cli') {
            $this->isAdmin = true;
        }

        parent::buildForm($builder, $options);

        $builder
            ->add('firstName', 'text', ['label' => 'first_name', 'read_only' => !$this->accesses['firstName'], 'disabled' => !$this->accesses['firstName']])
            ->add('lastName', 'text', ['label' => 'last_name',  'read_only' => !$this->accesses['lastName'], 'disabled' => !$this->accesses['lastName']])
            ->add('username', 'text', ['read_only' => true, 'disabled' => true, 'label' => 'username', 'read_only' => !$this->accesses['username'], 'disabled' => !$this->accesses['username']])
            ->add(
                'administrativeCode',
                'text',
                ['required' => false, 'read_only' => !$this->accesses['administrativeCode'], 'disabled' => !$this->accesses['administrativeCode'], 'label' => 'administrative_code']
            )
            ->add('mail', 'email', ['required' => false, 'label' => 'email', 'read_only' => !$this->accesses['email'], 'disabled' => !$this->accesses['email']])
            ->add('phone', 'text', ['required' => false, 'label' => 'phone', 'read_only' => !$this->accesses['phone'], 'disabled' => !$this->accesses['phone']])
            ->add('locale', 'choice', ['choices' => $this->langs, 'required' => false, 'label' => 'language'])
            ->add(
                'pictureFile',
                'file',
                [
                    'required' => false,
                    'constraints' => new Image(
                        [
                            'minWidth' => 50,
                            'maxWidth' => 800,
                            'minHeight' => 50,
                            'maxHeight' => 800,
                        ]
                    ),
                    'label' => 'picture_profile',
                    'read_only' => !$this->accesses['picture'],
                    'disabled' => !$this->accesses['picture'],
                ]
            )
            ->add(
                'description',
                'tinymce',
                ['required' => false, 'label' => 'description',  'read_only' => !$this->accesses['description'], 'disabled' => !$this->accesses['description']]
            )
            ->add(
                'organizations',
                'organization_picker',
                [
                   'label' => 'organizations',
                ]
            );

        if ($this->isAdmin || $this->isGrantedUserAdministration) {
            $isAdmin = $this->isAdmin;
            $builder
                ->add('firstName', 'text', ['label' => 'first_name'])
                ->add('lastName', 'text', ['label' => 'last_name'])
                ->add('username', 'text', ['label' => 'username'])
                ->add('administrativeCode', 'text', ['required' => false, 'label' => 'administrative_code'])
                ->add('mail', 'email', ['required' => false, 'label' => 'email'])
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
                        'mapped' => false,
                        'data' => $this->platformRoles,
                        'class' => 'Claroline\CoreBundle\Entity\Role',
                        'choice_translation_domain' => true,
                        'expanded' => true,
                        'multiple' => true,
                        'property' => 'translationKey',
                        'query_builder' => function (RoleRepository $roleRepository) use ($isAdmin) {
                            $query = $roleRepository->createQueryBuilder('r')
                                    ->where('r.type = '.Role::PLATFORM_ROLE)
                                    ->andWhere("r.name != 'ROLE_ANONYMOUS'")
                                    ->andWhere("r.name != 'ROLE_USER'");
                            if (!$isAdmin) {
                                $query->andWhere("r.name != 'ROLE_ADMIN'");
                            }

                            return $query;
                        },
                        'label' => 'roles',
                    ]
                )
                ->add(
                    'pictureFile',
                    'file',
                    [
                        'required' => false,
                        'constraints' => new Image(
                            [
                                'minWidth' => 50,
                                'maxWidth' => 800,
                                'minHeight' => 50,
                                'maxHeight' => 800,
                            ]
                        ),
                        'label' => 'picture_profile',
                    ]
                )
                ->add(
                    'description',
                    'tinymce',
                    ['required' => false, 'label' => 'description']
                )
                ->add(
                    'expirationDate',
                    'date',
                    [
                        'disabled' => false,
                        'widget' => 'single_text',
                        //'format' => $dateFormat,
                        'label' => 'expiration_date',
                    ]
                )
                ->add(
                    'organizations',
                    'organization_picker',
                    [
                       'label' => 'organizations',
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
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = [
            'data_class' => 'Claroline\CoreBundle\Entity\User',
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
