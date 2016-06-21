<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
        $builder->add('announcer', 'text', array('required' => false));
        $builder->add('content', 'tinymce', array('required' => true));
        $builder->add(
            'visible',
            'checkbox',
            array(
                'required' => false,
                'attr' => array('class' => 'visible-chk'),
            )
        );

        $attr = array();
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';

        $builder->add(
            'visible_from',
            'date',
            array(
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime',
            )
        );
        $builder->add(
            'visible_until',
            'date',
            array(
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime',
            )
        );
        $builder->add(
            'notify_user', 'checkbox', array(
                'label' => 'notify_user',
                'required' => false,
                'mapped' => false,
            )
        );
    }

    public function getName()
    {
        return 'announcement_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'translation_domain' => 'announcement',
            )
        );
    }
}
