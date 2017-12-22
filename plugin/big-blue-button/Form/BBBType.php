<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\BigBlueButtonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class BBBType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'label' => 'name',
                'constraints' => new NotBlank(),
                'attr' => ['autofocus' => true],
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'published',
            'checkbox',
            [
                'label' => 'publish_resource',
                'required' => true,
                'mapped' => false,
                'attr' => ['checked' => 'checked'],
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'roomName',
            'text',
            [
                'label' => 'room_name',
                'attr' => ['autofocus' => true],
            ]
        );
        $builder->add(
            'welcomeMessage',
            'text',
            [
                'label' => 'welcome_message',
                'attr' => ['autofocus' => true],
            ]
        );
        $builder->add(
            'newTab',
            'checkbox',
            [
                'label' => 'open_bbb_in_new_tab',
                'mapped' => true,
            ]
        );
        $builder->add(
            'moderatorRequired',
            'checkbox',
            [
                'label' => 'wait_for_moderator',
                'mapped' => true,
            ]
        );
        $builder->add(
            'record',
            'checkbox',
            [
                'label' => 'allow_recording',
                'mapped' => true,
            ]
        );
        $builder->add(
            'startDate',
            'text',
            [
                'label' => 'start_date',
                'mapped' => false,
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'endDate',
            'text',
            [
                'label' => 'end_date',
                'mapped' => false,
                'translation_domain' => 'platform',
            ]
        );
    }

    public function getName()
    {
        return 'bbb_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'bbb']);
    }
}
