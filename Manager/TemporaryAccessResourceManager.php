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
        if ($user === null) {
            return 'anonymous';
        } else {
            return $user->getId();
        }
    }

    public function hasTemporaryAccessOnSomeResources(User $user = null) {
        $temporaryAccessArray = $this->container->get('request')->getSession()->get(TemporaryAccessResourceManager::RESOURCE_TEMPORARY_ACCESS_KEY);

        if ($temporaryAccessArray == null or count($temporaryAccessArray) == 0) {
            return false;
        } else {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];

            return $temporaryAccessIds !== null and count($temporaryAccessIds) > 0;
        }
    }

    public function hasTemporaryAccess(ResourceNode $resource, User $user = null) {
        $temporaryAccessArray = $this->container->get('request')->getSession()->get(TemporaryAccessResourceManager::RESOURCE_TEMPORARY_ACCESS_KEY);

        if ($temporaryAccessArray == null or count($temporaryAccessArray) == 0) {
            return false;
        } else {
            $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];

            if ($temporaryAccessIds == null or count($temporaryAccessIds) == 0) {
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

        return true;
    }

    public function addTemporaryAccess(ResourceNode $node, User $user = null)
    {
        $temporaryAccessArray = $this->container->get('request')->getSession()->get(TemporaryAccessResourceManager::RESOURCE_TEMPORARY_ACCESS_KEY);

        if ($temporaryAccessArray == null) {
            $temporaryAccessArray = array();
        }

        $temporaryAccessIds = $temporaryAccessArray[$this->getUserKey($user)];
        if ($temporaryAccessIds == null) {
            $temporaryAccessIds = array();
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
        $this->container->get('request')->getSession()->set(TemporaryAccessResourceManager::RESOURCE_TEMPORARY_ACCESS_KEY, $temporaryAccessArray);

        echo('<pre>');
        var_dump($temporaryAccessArray);
        echo('</pre>');
        die();
    }
}
