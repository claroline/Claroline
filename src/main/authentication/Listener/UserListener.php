<?php

namespace Claroline\AuthenticationBundle\Listener;

use Claroline\AuthenticationBundle\Manager\OauthManager;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\LogUserDeleteEvent;

class UserListener
{
    /** @var OauthManager */
    private $oauthManager;

    public function __construct(OauthManager $oauthManager)
    {
        $this->oauthManager = $oauthManager;
    }

    public function onDelete(LogGenericEvent $event)
    {
        if ($event instanceof LogUserDeleteEvent) {
            $receiver = $event->getReceiver();
            if (null !== $receiver) {
                $this->oauthManager->unlinkAccount($receiver->getId());
            }
        }
    }
}
