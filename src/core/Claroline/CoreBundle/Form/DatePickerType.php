<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.datepicker")
 * @DI\FormType(alias = "datepicker")
 */
class DatePickerType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'translation_domain' => 'platform',
            'input'              => 'datetime',
            'widget'             => 'single_text',
            'component'          => false
        ));
    }

    public function getParent()
    {
        return 'date';
    }

    public function getName()
    {
        return 'datepicker';
    }
}