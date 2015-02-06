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
            ->add('title', 'text', array('required' => true));

        $builder->add(
            'isTask',
            'checkbox',
            array(
                'label' => 'isTask'
            )
        );

        $builder->add(
                'start',
                'datepicker',
                array(
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => $this->translator->trans('date_agenda_display_format_for_form', array(), 'platform'),
                    'attr'      => $attr,
                    'autoclose' => true
                    )
                );
        if (!$this->editMode) {
            $builder->add(
                'startHours',
                'time',
                array(
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
                'required'  => false,
                'widget'    => 'single_text',
                'format'    => $this->translator->trans('date_agenda_display_format_for_form', array(), 'platform'),
                'attr'      => $attr,
                'autoclose' => true
            )
        );

        if (!$this->editMode) {
            $builder->add(
                'endHours',
                'time',
                array(
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
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            );
        }

        //$builder->add('allDay', 'checkbox');
        $builder->add('description', 'tinymce')
            ->add(
                'priority',
                'choice',
                array(
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
            array('choices' => $recurring)
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
