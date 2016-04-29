<?php

namespace Innova\CollecticielBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("innova.temporary_access_resource_manager")
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
        if ($user === null) {
            return 'anonymous';
        } else {
            return $user->getId();
        }
    }

    public function hasTemporaryAccessOnSomeResources(User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request')->getSession()->get(self::RESOURCE_TEMPORARY_ACCESS_KEY);

        if ($temporaryAccessArray == null || count($temporaryAccessArray) == 0) {
            return false;
        } else {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];

            return $temporaryAccessIds !== null && count($temporaryAccessIds) > 0;
        }
    }

    public function hasTemporaryAccess(ResourceNode $resource, User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request')->getSession()->get(self::RESOURCE_TEMPORARY_ACCESS_KEY);

        if ($temporaryAccessArray == null || count($temporaryAccessArray) == 0) {
            return false;
        } else {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];

            if ($temporaryAccessIds == null || count($temporaryAccessIds) == 0) {
                return false;
            } else {
                foreach ($temporaryAccessIds as $temporaryAccessId) {
                    if ($temporaryAccessId == $resource->getId()) {
                        return true;
                    }
                }

                return false;
            }
        }
    }

    public function addTemporaryAccess(ResourceNode $node, User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request')->getSession()->get(self::RESOURCE_TEMPORARY_ACCESS_KEY);

        if ($temporaryAccessArray === null) {
            $temporaryAccessArray = array();
        }

        $temporaryAccessIds = array();
        if (isset($temporaryAccessArray[$this->getUserKey($user)])) {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];
        }

        $alreadyIn = false;
        foreach ($temporaryAccessIds as $temporaryAccessId) {
            if ($temporaryAccessId == $node->getId()) {
                $alreadyIn = true;
                break;
            }
        }
        if ($alreadyIn == false) {
            $temporaryAccessIds[] = $node->getId();
            $temporaryAccessArray[$this->getUserKey($user)] = $temporaryAccessIds;
        }
        $this->container->get('request')->getSession()
        ->set(self::RESOURCE_TEMPORARY_ACCESS_KEY, $temporaryAccessArray);
    }
}
