<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PlatformParametersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('selfRegistration', 'checkbox', array('required' => false));
        $builder->add('localLanguage', 'choice', array(
            'choices' => array('en' => 'en', 'fr' => 'fr')
            )
        );
        $builder->add('theme', 'choice', array(
            'choices' => array(
                'bootstrap-default' => 'bootstrap-default',
                'bootswatch-cyborg' => 'bootswatch-cyborg',
                'claroline' => 'claroline'
            )
        ));
    }

    public function getName()
    {
        return 'platform_parameters_form';
    }
}