<?php

namespace Claroline\LogBundle\Component\Log;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\CoreBundle\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractMessageLog implements EventSubscriberInterface, ComponentInterface
{
    use LogComponentTrait;

    /**
     * Utility method to create a new log.
     *
     * Note :
     *     - If $doer is not set, the method will try to retrieve it from the TokenStorage.
     *     - We allow to set the doer through params for some edge cases where the doer is not the current user.
     */
    protected function log(string $message, User $receiver, User $doer = null): void
    {
        if (empty($doer)) {
            $doer = $this->getCurrentUser();
        }

        $this->logManager->logMessage(static::getName(), $message, $doer, $receiver);
    }
}
