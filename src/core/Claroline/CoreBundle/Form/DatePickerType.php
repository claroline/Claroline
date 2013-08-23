<?php

namespace Claroline\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.form.datepicker")
 * @DI\FormType(alias = "datepicker")
 */
class DatePickerType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['component'] = $options['component'];
        $view->vars['options']   = array(
            'autoclose' => $options['autoclose'],
            'language'  => $options['language']
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'input'              => 'datetime',
                'widget'             => 'single_text',
                'component'          => false,
                'autoclose'          => false,
                'language'           => 'en'
            )
        );
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
