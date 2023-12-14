<?php

namespace Claroline\LogBundle\Component\Log;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractFunctionalLog implements EventSubscriberInterface, ComponentInterface
{
    use LogComponentTrait;

    /**
     * Utility method to create a new log.
     *
     * Note :
     *     - If $doer is not set, the method will try to retrieve it from the TokenStorage.
     *     - We allow to set the doer through params for some edge cases where the doer is not the current user.
     */
    protected function log(string $message, Workspace $workspace = null, ResourceNode $resourceNode = null, User $doer = null): void
    {
        if (empty($doer)) {
            $doer = $this->getCurrentUser();
        }

        $this->logManager->logFunctional(static::getName(), $message, $doer, $workspace, $resourceNode);
    }
}
