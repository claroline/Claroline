<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Form;

use Claroline\CursusBundle\Entity\CoursesWidgetConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class MyCoursesWidgetConfigurationType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'defaultMode',
            'choice',
            [
                'multiple' => false,
                'choices' => [
                    CoursesWidgetConfig::MODE_LIST => $this->translator->trans('list_view', [], 'cursus'),
                    CoursesWidgetConfig::MODE_CALENDAR => $this->translator->trans('calendar_view', [], 'cursus'),
                ],
                'label' => 'default_mode',
            ]
        );
    }

    public function getName()
    {
        return 'my_courses_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
