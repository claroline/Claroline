<?php

namespace Claroline\CoreBundle\Event\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched when a ResourceNode is serialized
 * in order to be returned in the JSON api.
 *
 * It's the right place to add custom data to the exported node.
 *
 * For example, it's used by the SocialMedia plugin to add the number of likes
 * obtained by the resource.
 *
 * We don't give direct access to the serialized data in order to avoid
 * possible data corruption by listeners.
 * Instead, dev must define a new key which will hold its custom data in the ResourceNode.
 */
class DecorateResourceNodeEvent extends Event
{
    /**
     * @var ResourceNode
     */
    private $resourceNode;

    /**
     * The list of keys listeners are not authorized to create
     * in order to avoid override of the default node data.
     *
     * @var array
     */
    private $unauthorizedKeys = [];

    /**
     * The list of data to inject in the resource node.
     *
     * In the form of :
     *  - key   : the key which will be used in the resourceNode structure.
     *            if the key has already been defined it will throw a RuntimeException.
     *  - value : the serializable data
     *
     * @var array
     */
    private $injectedData = [];

    /**
     * DecorateResourceNodeEvent constructor.
     *
     * @param ResourceNode $resourceNode     - the resource node being serialized
     * @param array        $unauthorizedKeys
     */
    public function __construct(
        ResourceNode $resourceNode,
        array $unauthorizedKeys = [])
    {
        $this->resourceNode = $resourceNode;
        $this->unauthorizedKeys = $unauthorizedKeys;
    }

    /**
     * Gets the resource node being serialized.
     *
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * Gets the data to inject.
     *
     * @return array
     */
    public function getInjectedData()
    {
        return $this->injectedData;
    }

    /**
     * Adds custom data to the resource node.
     *
     * @param string $key
     * @param mixed  $data
     */
    public function add($key, $data)
    {
        $this->validate($key, $data);

        $this->injectedData[$key] = $data;
    }

    /**
     * Validates injected data.
     *
     * @param string $key
     * @param mixed  $data
     */
    private function validate($key, $data)
    {
        // validates key
        if (in_array($key, $this->unauthorizedKeys)) {
            throw new \RuntimeException(
                'Injected key `'.$key.'` is not authorized. (Unauthorized keys: '.implode(', ', $this->unauthorizedKeys).')'
            );
        }

        if (in_array($key, array_keys($this->injectedData))) {
            throw new \RuntimeException(
                'Injected key `'.$key.'` is already used.'
            );
        }

        // validates data (must be serializable)
        if (false !== $data && false === json_encode($data)) {
            throw new \RuntimeException(
                'Injected data is not serializable.'
            );
        }
    }
}
