<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PlatformParametersType extends AbstractType
{
    protected $themes;

    public function __construct($themes)
    {
        $this->themes = $themes;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selfRegistration', 'checkbox', array('required' => false))
            ->add(
                'localLanguage',
                'choice',
                array(
                    'choices' => array('en' => 'en', 'fr' => 'fr')
                )
            )
            ->add(
                'theme',
                'choice',
                array(
                    'choices' => $this->themes
                )
            );
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'translation_domain' => 'platform'
        );
    }
}
