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
    private $langs;

    /**
     * Constructor.
     *
     * @param Role[]  $platformRoles
     * @param boolean $isAdmin
     * @param string[]   $langs
     */
    public function __construct(array $platformRoles, $isAdmin, array $langs)
    {
        $this->platformRoles = new ArrayCollection($platformRoles);
        $this->isAdmin = $isAdmin;

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('firstName', 'text', array('label' => 'First name'))
            ->add('lastName', 'text', array('label' => 'Last name'));

        if (!$this->isAdmin) {
            $builder
                ->add('username', 'text', array('read_only' => true, 'disabled' => true, 'label' => 'User name'))
                ->add('administrativeCode', 'text', array('required' => false, 'read_only' => true, 'disabled' => true))
                ->add('mail', 'email', array('required' => false))
                ->add('phone', 'text', array('required' => false))
                ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'Language'));

        } else {
            $builder
                ->add('username', 'text', array('label' => 'User name'))
                ->add('administrativeCode', 'text', array('required' => false))
                ->add('mail', 'email', array('required' => false))
                ->add('phone', 'text', array('required' => false))
                ->add('locale', 'choice', array('choices' => $this->langs, 'required' => false, 'label' => 'Language'))
                ->add(
                    'platformRoles',
                    'entity',
                    array(
                        'mapped' => false,
                        'data' => $this->platformRoles,
                        'class' => 'Claroline\CoreBundle\Entity\Role',
                        'expanded' => false,
                        'multiple' => true,
                        'property' => 'translationKey',
                        'query_builder' => function (RoleRepository $roleRepository) {
                            return $roleRepository->createQueryBuilder('r')
                                    ->where("r.type != " . Role::WS_ROLE)
                                    ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                        }
                    )
                );
        }
        $builder->add(
            'pictureFile',
            'file',
            array(
                'required' => false,
                'constraints' => new Image(
                    array(
                        'minWidth' => 50,
                        'maxWidth' => 800,
                        'minHeight' => 50,
                        'maxHeight' => 800,
                    )
                )
            )
        )

        ->add(
            'description',
            'tinymce',
            array('required' => false)
        );
    }

    public function getName()
    {
        return 'profile_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Claroline\CoreBundle\Entity\User',
                'translation_domain' => 'platform'
            )
        );
    }
}
