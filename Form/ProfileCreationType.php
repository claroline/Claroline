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
    private $isAdmin;
    private $langs;

     /**
      * Constructor.
      *
      * @param Role[]  $platformRoles
      */
    public function __construct(array $platformRoles, array $langs)
    {
        $this->platformRoles = new ArrayCollection($platformRoles);

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

            $builder->add('firstName', 'text', array('label' => 'profile_form_firstName'))
                ->add('lastName', 'text', array('label' => 'profile_form_lastName'))
                ->add('username', 'text', array('label' => 'profile_form_username'))
                ->add('plainPassword', 'repeated', array('type' => 'password', 'required' => true))
                ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false))
                ->add('administrativeCode', 'text', array('required' => false, 'label' => 'profile_form_administrativeCode'))
                ->add('mail', 'email', array('required' => true, 'label' => 'profile_form_mail'))
                ->add('phone', 'text', array('required' => false, 'label' => 'profile_form_phone'))
                ->add(
                'platformRoles',
                'entity',
                    array(
                        'label' => 'profile_form_platformRoles',
                        'mapped' => false,
                        'data' => $this->platformRoles,
                        'class' => 'Claroline\CoreBundle\Entity\Role',
                        'expanded' => false,
                        'multiple' => true,
                        'property' => 'translationKey',
                        'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                            return $er->createQueryBuilder('r')
                                    ->where("r.type != " . Role::WS_ROLE)
                                    ->andWhere("r.name != 'ROLE_ANONYMOUS'");
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
        $resolver->setDefaults(array(
            'data_class' => 'Claroline\CoreBundle\Entity\User',
            'translation_domain' => 'platform'
        ));
    }
}
