<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class ClarolineSettingsType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('allow self registration', 'choice', array (
            'choices' => array('true' => 'true', 'false' => 'false')
            )
        );
        $builder->add('language', 'choice', array(
            'choices' => array('en' => 'EN', 'fr' => 'FR')
            )
        );
    }

    public function getName()
    {
        return 'claro_settings_form';
    }
}