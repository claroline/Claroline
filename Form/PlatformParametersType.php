<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PlatformParametersType extends AbstractType
{
    private $themes;

    public function __construct(array $themes)
    {
        $this->themes = $themes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => false))
            ->add('support_email', 'email', array('label' => 'support_email'))
            ->add('footer', 'text', array('required' => false))
            ->add('selfRegistration', 'checkbox')
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
