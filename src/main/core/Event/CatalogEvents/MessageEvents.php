<?php

namespace Claroline\CoreBundle\Event\CatalogEvents;

final class MessageEvents
{
    /**
     * @Event("Claroline\CoreBundle\Event\SendMessageEvent")
     */
    public const MESSAGE_SENDING = 'claroline_message_sending';
}
