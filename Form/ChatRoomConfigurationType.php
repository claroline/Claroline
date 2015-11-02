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
    private $chatRoom;
    private $configHandler;

    public function __construct(ChatRoom $chatRoom, PlatformConfigurationHandler $configHandler)
    {
        $this->chatRoom = $chatRoom;
        $this->configHandler = $configHandler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $xmppMucHost = $this->configHandler->getParameter('chat_xmpp_muc_host');
        $chatRoomId = $this->chatRoom->getRoomName() . '@' . $xmppMucHost;

        $builder->add(
            'chatRoomId',
            'text',
            array(
                'mapped' => false,
                'label' => 'chat_room_id',
                'disabled' => true,
                'data' => $chatRoomId
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
