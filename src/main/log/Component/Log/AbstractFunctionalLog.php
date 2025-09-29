<?php

namespace Claroline\LogBundle\Component\Log;

use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\CoreBundle\Entity\User;
use Doctrine\Persistence\Proxy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractFunctionalLog implements EventSubscriberInterface, ComponentInterface
{
    use LogComponentTrait;

    /**
     * Utility method to create a new log.
     *
     * Note:
     *     - If $doer is not set, the method will try to retrieve it from the TokenStorage.
     *     - We allow setting the doer through params for some edge cases where the doer is not the current user.
     */
    protected function log(string $message, ?object $object = null, User $doer = null): void
    {
        if (empty($doer)) {
            $doer = $this->getCurrentUser();
        }

        $this->logManager->logFunctional(
            static::getName(),
            $message,
            $doer,
            static::getRealClass($object),
            $object->getUuid(),
            $this->getContextId($object)
        );
    }

    protected function getContextId(?object $object = null): ?string
    {
        return null;
    }

    private static function getRealClass(object $entity): string
    {
        return $entity instanceof Proxy
            ? get_parent_class($entity)
            : get_class($entity);
    }
}
