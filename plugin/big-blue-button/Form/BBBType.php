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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BBBType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label' => 'name',
                'constraints' => new NotBlank(),
                'attr' => ['autofocus' => true],
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'published',
            CheckboxType::class,
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
            TextType::class,
            [
                'label' => 'room_name',
                'attr' => ['autofocus' => true],
            ]
        );
        $builder->add(
            'welcomeMessage',
            TextType::class,
            [
                'label' => 'welcome_message',
                'attr' => ['autofocus' => true],
            ]
        );
        $builder->add(
            'newTab',
            CheckboxType::class,
            [
                'label' => 'open_bbb_in_new_tab',
                'mapped' => true,
            ]
        );
        $builder->add(
            'moderatorRequired',
            CheckboxType::class,
            [
                'label' => 'wait_for_moderator',
                'mapped' => true,
            ]
        );
        $builder->add(
            'record',
            CheckboxType::class,
            [
                'label' => 'allow_recording',
                'mapped' => true,
            ]
        );
        $builder->add(
            'startDate',
            TextType::class,
            [
                'label' => 'start_date',
                'mapped' => false,
                'translation_domain' => 'platform',
            ]
        );
        $builder->add(
            'endDate',
            TextType::class,
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'bbb']);
    }
}
