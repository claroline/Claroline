<?php
/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form\Field;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\DiExtraBundle\Annotation\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\Service("claroline.form.datetimepicker")
 * @Tag("form.type")
 */
class DateTimePickerType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['component'] = $options['component'];
        $view->vars['options'] = [
            'autoclose' => $options['autoclose'],
            'language' => $options['language'],
            'date_format' => $options['format'],
        ];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'with_seconds' => false,
                'language' => 'en',
                'component' => true,
                'autoclose' => true,
                'date_format' => DateType::HTML5_FORMAT,
            ]
        );
    }

    public function getParent()
    {
        return 'datetime';
    }

    public function getName()
    {
        return 'datetimepicker';
    }
}
