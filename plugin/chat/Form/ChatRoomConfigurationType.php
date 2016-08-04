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
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChatRoomConfigurationType extends AbstractType
{
    private $configHandler;

    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $typesList = [
            ChatRoom::TEXT => 'text_only',
        ];
        $statusList = [
            ChatRoom::UNINITIALIZED => 'uninitialized',
            ChatRoom::OPEN => 'open',
            ChatRoom::CLOSED => 'closed',
        ];
        $disableAudio = $this->configHandler->getParameter('chat_room_audio_disable');
        $disableVideo = $this->configHandler->getParameter('chat_room_video_disable');

        if (!$disableAudio) {
            $typesList[ChatRoom::AUDIO] = 'audio_only';
        }

        if (!$disableVideo) {
            $typesList[ChatRoom::VIDEO] = 'audio_video';
        }

        $builder->add(
            'roomName',
            'text',
            [
                'label' => 'chat_room_id',
                'disabled' => true,
            ]
        );
        $builder->add(
            'roomType',
            'choice',
            [
                'label' => 'type',
                'choices' => $typesList,
            ]
        );
        $builder->add(
            'roomStatus',
            'choice',
            [
                'label' => 'status',
                'choices' => $statusList,
            ]
        );
    }

    public function getName()
    {
        return 'chat_room_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'chat']);
    }
}
