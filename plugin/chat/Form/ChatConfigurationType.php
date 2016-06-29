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

use Claroline\ChatBundle\Validator\Constraints\BoshPort;
use Claroline\ChatBundle\Validator\Constraints\XmppHost;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChatConfigurationType extends AbstractType
{
    private $configHandler;

    public function __construct(PlatformConfigurationHandler $configHandler)
    {
        $this->configHandler = $configHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $xmppHost = $this->configHandler->getParameter('chat_xmpp_host');
        $xmppMucHost = $this->configHandler->getParameter('chat_xmpp_muc_host');
        $configPort = $this->configHandler->getParameter('chat_bosh_port');
        $boshPort = empty($configPort) ? 5280 : $configPort;
        $configIceServers = $this->configHandler->getParameter('chat_ice_servers');
        $iceServers = empty($configIceServers) ?
            'stun:stun.l.google.com:19302' :
            $configIceServers;
        $chatRoomAudio = $this->configHandler->getParameter('chat_room_audio_disable');
        $chatRoomVideo = $this->configHandler->getParameter('chat_room_video_disable');
        $admin = $this->configHandler->getParameter('chat_admin_username');
        $password = $this->configHandler->getParameter('chat_admin_password');
        $disableAudio = is_null($chatRoomAudio) ? false : $chatRoomAudio;
        $disableVideo = is_null($chatRoomAudio) ? false : $chatRoomVideo;

        $builder->add(
            'host',
            'text',
            [
                'required' => false,
                'data' => $xmppHost,
                'mapped' => false,
                'label' => 'host',
                'constraints' => [new NotBlank(), new XmppHost()],
            ]
        );
        $builder->add(
            'admin',
            'text',
            [
                'required' => false,
                'data' => $admin,
                'mapped' => false,
                'label' => 'admin',
                'constraints' => [new NotBlank()],
            ]
        );
        $builder->add(
            'password',
            'password',
            [
                'required' => false,
                'data' => $password,
                'mapped' => false,
                'label' => 'password',
                'constraints' => [new NotBlank()],
            ]
        );
        $builder->add(
            'mucHost',
            'text',
            [
                'required' => false,
                'data' => $xmppMucHost,
                'mapped' => false,
                'label' => 'muc_host',
                'constraints' => [new NotBlank()],
            ]
        );
        $builder->add(
            'port',
            'integer',
            [
                'required' => true,
                'data' => $boshPort,
                'mapped' => false,
                'label' => 'bosh_server_port',
                'constraints' => [new NotBlank(), new BoshPort()],
            ]
        );
        $builder->add(
            'iceServers',
            'text',
            [
                'required' => false,
                'data' => $iceServers,
                'mapped' => false,
                'label' => 'ice_servers',
                'constraints' => [new NotBlank()],
            ]
        );
        $builder->add(
            'disableChatRoomAudio',
            'checkbox',
            [
                'required' => false,
                'data' => $disableAudio,
                'mapped' => false,
                'label' => 'disable_chat_room_audio',
            ]
        );
        $builder->add(
            'disableChatRoomVideo',
            'checkbox',
            [
                'required' => false,
                'data' => $disableVideo,
                'mapped' => false,
                'label' => 'disable_chat_room_video',
            ]
        );
    }

    public function getName()
    {
        return 'chat_configuration_form';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['translation_domain' => 'chat']);
    }
}
