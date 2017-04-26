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
        $builder->add('title', 'text', ['required' => false]);
        $builder->add('announcer', 'text', ['required' => false]);
        $builder->add('content', 'tinymce', ['required' => true]);
        $builder->add(
            'visible',
            'checkbox',
            [
                'required' => false,
                'attr' => ['class' => 'visible-chk'],
            ]
        );

        $attr = [];
        $attr['class'] = 'datepicker input-small';
        $attr['data-date-format'] = 'dd-mm-yyyy';
        $attr['autocomplete'] = 'off';

        $builder->add(
            'visible_from',
            'date',
            [
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime',
            ]
        );
        $builder->add(
            'visible_until',
            'date',
            [
                'required' => false,
                'format' => 'dd-MM-yyyy',
                'widget' => 'single_text',
                'attr' => $attr,
                'input' => 'datetime',
            ]
        );
        $builder->add(
            'notify_user', 'checkbox', [
                'label' => 'send_email_to_users',
                'required' => false,
                'mapped' => false,
            ]
        );
    }

    public function getName()
    {
        return 'announcement_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'translation_domain' => 'announcement',
            ]
        );
    }
}
