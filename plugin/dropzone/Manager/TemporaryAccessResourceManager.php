<?php

namespace Icap\DropzoneBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.temporary_access_resource_manager")
 */
class TemporaryAccessResourceManager
{
    const RESOURCE_TEMPORARY_ACCESS_KEY = 'RESOURCE_TEMPORARY_ACCESS_KEY';

    private $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    private function getUserKey(User $user)
    {
        if (null === $user) {
            return 'anonymous';
        } else {
            return $user->getId();
        }
    }

    public function hasTemporaryAccessOnSomeResources(User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request_stack')->getMasterRequest()->getSession()->get(self::RESOURCE_TEMPORARY_ACCESS_KEY);

        if (null === $temporaryAccessArray || 0 === count($temporaryAccessArray)) {
            return false;
        } else {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];

            return null !== $temporaryAccessIds && count($temporaryAccessIds) > 0;
        }
    }

    public function hasTemporaryAccess(ResourceNode $resource, User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request_stack')->getMasterRequest()->getSession()->get(self::RESOURCE_TEMPORARY_ACCESS_KEY);

        if (null === $temporaryAccessArray || 0 === count($temporaryAccessArray)) {
            return false;
        } else {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];

            if (null === $temporaryAccessIds || 0 === count($temporaryAccessIds)) {
                return false;
            } else {
                foreach ($temporaryAccessIds as $temporaryAccessId) {
                    if ($temporaryAccessId === $resource->getId()) {
                        return true;
                    }
                }

                return false;
            }
        }

        return true;
    }

    public function addTemporaryAccess(ResourceNode $node, User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request_stack')->getMasterRequest()->getSession()->get(self::RESOURCE_TEMPORARY_ACCESS_KEY);

        if (null === $temporaryAccessArray) {
            $temporaryAccessArray = [];
        }

        $temporaryAccessIds = [];
        if (isset($temporaryAccessArray[$this->getUserKey($user)])) {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];
        }

        $alreadyIn = false;
        foreach ($temporaryAccessIds as $temporaryAccessId) {
            if ($temporaryAccessId === $node->getId()) {
                $alreadyIn = true;
                break;
            }
        }
        if (false === $alreadyIn) {
            $temporaryAccessIds[] = $node->getId();
            $temporaryAccessArray[$this->getUserKey($user)] = $temporaryAccessIds;
        }
        $this->container->get('request_stack')->getMasterRequest()->getSession()
        ->set(self::RESOURCE_TEMPORARY_ACCESS_KEY, $temporaryAccessArray);
    }
}
