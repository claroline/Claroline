<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Form;

use Claroline\ChatBundle\Entity\ChatRoom;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChatRoomConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typesList = array(
            ChatRoom::TEXT => 'text_only',
            ChatRoom::AUDIO => 'audio_only',
            ChatRoom::VIDEO => 'audio_video'
        );
        $statusList = array(
            ChatRoom::UNINITIALIZED => 'uninitialized',
            ChatRoom::OPEN => 'open',
            ChatRoom::CLOSED => 'closed'
        );

        $builder->add(
            'roomName',
            'text',
            array(
                'label' => 'chat_room_id',
                'disabled' => true
            )
        );
        $builder->add(
            'roomType',
            'choice',
            array(
                'label' => 'type',
                'choices' => $typesList
            )
        );
        $builder->add(
            'roomStatus',
            'choice',
            array(
                'label' => 'status',
                'choices' => $statusList
            )
        );
    }

    public function getName()
    {
        return 'chat_room_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('translation_domain' => 'chat'));
    }
}
