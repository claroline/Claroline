<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Form;

use Claroline\CoreBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AgendaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $recurring = array();

        for ($i = 0; $i < 10; $i++) {
            $recurring[$i] = $i;
        }

        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';
        $builder
            ->add('title', 'text', array('required' => true))
            ->add(
                'start',
                'datepicker',
                array(
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'dd-MM-yyyy',
                    'attr'      => $attr,
                    'autoclose' => true
                    )
                )
            ->add(
                'startHours',
                'time',
                array(
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            )
            ->add(
                'end',
                'datepicker',
                array(
                    'required'  => false,
                    'widget'    => 'single_text',
                    'format'    => 'dd-MM-yyyy',
                    'attr'      => $attr,
                    'autoclose' => true
                )
            )
            ->add(
                'endHours',
                'time',
                array(
                    'attr' => array('class' => 'hours'),
                    'input' => 'timestamp',
                    'widget' => 'single_text'
                )
            )
            ->add('allDay', 'checkbox')
            ->add('description', 'tinymce')
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
            )
            ->add(
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
