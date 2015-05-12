<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AgendaBundle\Form;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Translation\TranslatorInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @DI\Service("claroline.form.agenda")
 */
class AgendaType extends AbstractType
{
    private $translator;
    private $editMode;

    /**
     * @DI\InjectParams({
     *     "translator" = @DI\Inject("translator")
     * })
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->editMode = false;
    }

    public function setEditMode()
    {
        $this->editMode = true;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $recurring = array();

        for ($i = 0; $i < 10; $i++) {
            $recurring[$i] = $i;
        }

        $now = new \DateTime();

        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = $this->translator->trans('date_form_datepicker_format', array(), 'platform');
        $attr['autocomplete'] = 'off';
        $builder
            ->add('title', 'text', array(
                'label' => 'form.title',
                'required' => true
            ));

        $builder->add(
            'isTask',
            'checkbox',
            array(
                'label' => 'form.task',
                'required' => false
            )
        );

        $builder->add(
            'isAllDay',
            'checkbox',
            array(
                'label' => 'form.all_day',
                'required' => false
            )
        );

        $builder->add(
                'start',
                'datepicker',
                array(
                    'label' => 'form.start',
                    'required'  => true,
                    'widget'    => 'single_text',
                    'format'    => 'date_agenda_display_format_for_form',
                    'attr'      => $attr,
                    'autoclose' => true,
                    'constraints' => new Assert\Date()
                    )
                );
        if (!$this->editMode) {
            $builder->add(
                'startHours',
                'time',
                array(
                    'label' => 'form.start_hours',
                    'data' => $now->getTimestamp(),
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            );
        } else {
            $builder->add(
                'startHours',
                'time',
                array(
                    'label' => 'form.start_hours',
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            );
        }

        $builder->add(
            'end',
            'datepicker',
            array(
                'label' => 'form.end',
                'required'  => true,
                'widget'    => 'single_text',
                'format'    => 'date_agenda_display_format_for_form',
                'attr'      => $attr,
                'autoclose' => true,
                'constraints' => new Assert\Date()
            )
        );

        if (!$this->editMode) {
            $builder->add(
                'endHours',
                'time',
                array(
                    'label' => 'form.end_hours',
                    'data' => $now->getTimestamp(),
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            );
        } else {
            $builder->add(
                'endHours',
                'time',
                array(
                    'label' => 'form.end_hours',
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            );
        }

        $builder->add(
            'description',
            'tinymce',
            array(
                'label' => 'form.description'
            )
        );

        $builder->add(
            'priority',
            'choice',
            array(
                'label' => 'form.priority',
                'choices' => array(
                    '#FF0000' => 'high',
                    '#01A9DB' => 'medium',
                    '#848484' => 'low'
                )
            )
        );

        $builder->add(
            'recurring',
            'choice',
            array(
                'label' => 'form.recurring',
                'choices' => $recurring
            )
        );
    }

    public function getName()
    {
        return 'agenda_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'workspace' => new Workspace() ,
                'user' => new User(),
                'class' => 'Claroline\CoreBundle\Entity\Event',
                'translation_domain' => 'agenda'
            )
        );
    }
}
