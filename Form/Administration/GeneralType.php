<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Administration;

use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GeneralType extends AbstractType
{
    private $langs;
    private $role;

    public function __construct(array $langs, $role)
    {
        $this->role = $role;

        if (!empty($langs)) {
            $this->langs = $langs;
        } else {
            $this->langs = array('en' => 'en', 'fr' => 'fr');
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => false))
            ->add(
                'description',
                'content',
                array(
                    'mapped' => false,
                    'required' => false,
                    'label' => 'Description',
                    'theme_options' => array('contentTitle' => false, 'tinymce' => false)
                )
            )
            ->add('support_email', 'email', array('label' => 'support_email'))
            ->add('selfRegistration', 'checkbox', array('required' => false))
            ->add(
                'defaultRole',
                'entity',
                array(
                    'mapped' => false,
                    'data' => $this->role,
                    'class' => 'Claroline\CoreBundle\Entity\Role',
                    'expanded' => false,
                    'multiple' => false,
                    'property' => 'translationKey',
                    'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('r')
                                ->where("r.type != " . Role::WS_ROLE)
                                ->andWhere("r.name != 'ROLE_ANONYMOUS'");
                    }
                )
            )
            ->add(
                'localeLanguage',
                'choice',
                array(
                    'choices' => $this->langs
                )
            )
            ->add('cookie_lifetime', 'number', array('required' => false));
   }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'platform'));
    }
}
