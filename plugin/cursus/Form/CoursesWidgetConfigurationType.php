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
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;

class CoursesWidgetConfigurationType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'cursus',
            'entity',
            [
                'class' => 'ClarolineCursusBundle:Cursus',
                'query_builder' => function (EntityRepository $er) {

                    return $er->createQueryBuilder('c')
                        ->where('c.course IS NULL')
                        ->orderBy('c.title', 'ASC');
                },
                'property' => 'titleAndCode',
                'required' => false,
                'label' => 'cursus',
            ]
        );
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
        $builder->add(
            'publicSessionsOnly',
            'checkbox',
            ['label' => 'public_sessions_only']
        );
    }

    public function getName()
    {
        return 'courses_widget_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'cursus']);
    }
}
