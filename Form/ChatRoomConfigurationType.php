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
use Symfony\Component\Translation\TranslatorInterface;

class ChatRoomConfigurationType extends AbstractType
{
    private $configHandler;
    private $translator;

    public function __construct(
        PlatformConfigurationHandler $configHandler,
        TranslatorInterface $translator
    )
    {
        $this->configHandler = $configHandler;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $statusList = array(
            ChatRoom::UNINITIALIZED => $this->translator->trans('chat_room_uninitialized', array(), 'chat'),
            ChatRoom::OPEN => $this->translator->trans('chat_room_open', array(), 'chat'),
            ChatRoom::CLOSED => $this->translator->trans('chat_room_closed', array(), 'chat'),
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
