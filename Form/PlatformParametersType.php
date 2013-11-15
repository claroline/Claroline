<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Claroline\CoreBundle\Entity\Role;

class PlatformParametersType extends AbstractType
{
    private $themes;
    private $role;

    public function __construct(array $themes, $role)
    {
        $this->themes = $themes;
        $this->role = $role;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => false))
            ->add('support_email', 'email', array('label' => 'support_email'))
            ->add('footer', 'text', array('required' => false))
            ->add('selfRegistration', 'checkbox')
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
                'localLanguage',
                'choice',
                array(
                    'choices' => array('en' => 'en', 'fr' => 'fr')
                )
            )
            ->add('theme', 'choice', array('choices' => $this->themes));
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
