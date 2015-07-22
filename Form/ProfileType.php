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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Image;

class ProfileType extends AbstractType
{
    private $platformRoles;
    private $isAdmin;
    private $isGrantedUserAdministration;
    private $langs;
    private $authenticationDrivers;
    private $accesses;

    /**
     * Constructor.
     *
     * @param Role[]   $platformRoles
     * @param boolean  $isAdmin
     * @param string[] $langs
     */
    public function __construct(
        $localeManager,
        array $platformRoles,
        $isAdmin,
        $isGrantedUserAdministration,
        $accesses,
        $authenticationDrivers = null
    )
    {
        $this->accesses = $accesses;
        $this->platformRoles = new ArrayCollection($platformRoles);
        $this->isAdmin = $isAdmin;
        $this->isGrantedUserAdministration = $isGrantedUserAdministration;
        $this->langs = $localeManager->retrieveAvailableLocales();
        $this->authenticationDrivers = $authenticationDrivers;
        $this->forApi = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (php_sapi_name() === 'cli') {
            $this->isAdmin = true;
        }
        
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName', 'text', array('label' => 'first_name', 'read_only' => !$this->accesses['firstName'], 'disabled' => !$this->accesses['firstName']))
            ->add('lastName', 'text', array('label' => 'last_name',  'read_only' => !$this->accesses['lastName'], 'disabled' => !$this->accesses['lastName']))
            ->add('username', 'text', array('read_only' => true, 'disabled' => true, 'label' => 'username', 'read_only' => !$this->accesses['username'], 'disabled' => !$this->accesses['username']))
            ->add(
                'administrativeCode',
                'text',
                array('required' => false, 'read_only' => !$this->accesses['administrativeCode'], 'disabled' => !$this->accesses['administrativeCode'], 'label' => 'administrative_code')
            )
            ->add('mail', 'email', array('required' => false, 'label' => 'email', 'read_only' => !$this->accesses['email'], 'disabled' => !$this->accesses['email']))
            ->add('phone', 'text', array('required' => false, 'label' => 'phone', 'read_only' => !$this->accesses['phone'], 'disabled' => !$this->accesses['phone']))
            ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'language'))
            ->add(
                'pictureFile',
                'file',
                array(
                    'required' => false,
                    'constraints' => new Image(
                        array(
                            'minWidth'  => 50,
                            'maxWidth'  => 800,
                            'minHeight' => 50,
                            'maxHeight' => 800,
                        )
                    ),
                    'label' => 'picture_profile',
                    'read_only' => !$this->accesses['picture'],
                    'disabled' => !$this->accesses['picture']
                )
            )
            ->add(
                'description',
                'tinymce',
                array('required' => false, 'label' => 'description',  'read_only' => !$this->accesses['description'], 'disabled' => !$this->accesses['description'])
            );

        if ($this->isAdmin || $this->isGrantedUserAdministration) {
            $isAdmin = $this->isAdmin;
            $builder
                ->add('firstName', 'text', array('label' => 'first_name'))
                ->add('lastName', 'text', array('label' => 'last_name'))
                ->add('username', 'text', array('label' => 'username'))
                ->add('administrativeCode', 'text', array('required' => false, 'label' => 'administrative_code'))
                ->add('mail', 'email', array('required' => false, 'label' => 'email'))
                ->add('phone', 'text', array('required' => false, 'label' => 'phone'))
                ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'language'))
                ->add(
                    'authentication',
                    'choice',
                    array(
                        'choices' => $this->authenticationDrivers,
                        'required' => false,
                        'label' => 'authentication'
                    )
                )
                ->add(
                    'platformRoles',
                    'entity',
                    array(
                        'mapped' => false,
                        'data' => $this->platformRoles,
                        'class' => 'Claroline\CoreBundle\Entity\Role',
                        'choice_translation_domain' => true,
                        'expanded' => true,
                        'multiple' => true,
                        'property' => 'translationKey',
                        'query_builder' => function (RoleRepository $roleRepository) use ($isAdmin) {
                            $query = $roleRepository->createQueryBuilder('r')
                                    ->where("r.type = " . Role::PLATFORM_ROLE)
                                    ->andWhere("r.name != 'ROLE_ANONYMOUS'")
                                    ->andWhere("r.name != 'ROLE_USER'");
                            if (!$isAdmin) {
                                $query->andWhere("r.name != 'ROLE_ADMIN'");
                            }

                            return $query;
                        },
                        'label' => 'roles'
                    )
                )
                ->add(
                    'pictureFile',
                    'file',
                    array(
                        'required' => false,
                        'constraints' => new Image(
                            array(
                                'minWidth'  => 50,
                                'maxWidth'  => 800,
                                'minHeight' => 50,
                                'maxHeight' => 800,
                            )
                        ),
                        'label' => 'picture_profile'
                    )
                )
                ->add(
                    'description',
                    'tinymce',
                    array('required' => false, 'label' => 'description')
                );
        }
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array(
            'data_class'         => 'Claroline\CoreBundle\Entity\User',
            'translation_domain' => 'platform'
        );
        if ($this->forApi) $default['csrf_protection'] = false;

        $resolver->setDefaults($default);
    }

    public function enableApi()
    {
        $this->forApi = true;
    }
}
