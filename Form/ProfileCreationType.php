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
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;

class ProfileCreationType extends AbstractType
{
    private $platformRoles;
    private $langs;
    private $isAdmin;

     /**
      * Constructor.
      *
      * @param Role[]  $platformRoles
      * @param array   $langs
      * @param boolean $isAdmin
      */
    public function __construct(array $platformRoles, array $langs, $isAdmin = false)
    {
        $this->platformRoles = new ArrayCollection($platformRoles);

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }

        $this->isAdmin = $isAdmin;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $isAdmin = $this->isAdmin;

            $builder->add('firstName', 'text', array('label' => 'First name'))
                ->add('lastName', 'text', array('label' => 'Last name'))
                ->add('username', 'text', array('label' => 'User name'))
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
                ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'Language'))
                ->add(
                    'platformRoles',
                    'entity',
                    array(
                        'label' => 'roles',
                        'mapped' => false,
                        'data' => $this->platformRoles,
                        'class' => 'Claroline\CoreBundle\Entity\Role',
                        'expanded' => true,
                        'multiple' => true,
                        'property' => 'translationKey',
                        'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($isAdmin) {
                            $query = $er->createQueryBuilder('r')
                                    ->where("r.type != " . Role::WS_ROLE)
                                    ->andWhere("r.name != 'ROLE_USER'")
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
        return 'profile_form_creation';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Claroline\CoreBundle\Entity\User',
                'validation_groups' => array('registration', 'Default'),
                'translation_domain' => 'platform'
            )
        );
    }
}
