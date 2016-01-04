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
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Manager\LocaleManager;

class ProfileCreationType extends AbstractType
{
    private $platformRoles;
    private $langs;
    private $isAdmin;
    private $authenticationDrivers;
    private $localeMnanager;

     /**
      * Constructor.
      *
      * @param Role[]  $platformRoles
      * @param array   $langs
      * @param boolean $isAdmin
      */
    public function __construct(
        $localeManager,
        array $platformRoles,
        $isAdmin = false,
        $authenticationDrivers = null
    )
    {
        $this->platformRoles = $platformRoles;
        $this->langs = $localeManager->retrieveAvailableLocales();
        $this->isAdmin = $isAdmin;
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

        $builder->add('firstName', 'text', array('label' => 'first_name'))
            ->add('lastName', 'text', array('label' => 'last_name'))
            ->add('username', 'text', array('label' => 'username'))
            ->add(
                'plainPassword',
                'repeated',
                array(
                    'type' => 'password',
                    'first_options' => array('label' => 'password'),
                    'second_options' => array('label' => 'verification')
                )
            )
            ->add(
                'administrativeCode',
                'text',
                array(
                    'required' => false, 'label' => 'administrative_code'
                )
            )
            ->add('mail', 'email', array('required' => true, 'label' => 'email'))
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
                    'label' => 'roles',
                    'choice_translation_domain' => true,
                    'mapped' => false,
                    'data' => $this->platformRoles,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'translationKey',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($isAdmin) {
                        $query = $er->createQueryBuilder('r')
                                ->where("r.type = " . Role::PLATFORM_ROLE)
                                ->andWhere("r.name != 'ROLE_USER'")
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                        if (!$isAdmin) {
                            $query->andWhere("r.name != 'ROLE_ADMIN'");
                        }

                        return $query;
                    }
                )
            )
            ->add(
                'organizations',
                'entity',
                array(
                    'label' => 'organizations',
                    'class' => 'Claroline\CoreBundle\Entity\Organization\Organization',
                    'expanded' => true,
                    'multiple' => true,
                    'property' => 'name'
                )
            );
    }

    public function getName()
    {
        return 'profile_form_creation';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $default = array(
            'data_class' => 'Claroline\CoreBundle\Entity\User',
            'validation_groups' => array('registration', 'Default'),
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
