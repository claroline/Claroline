<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;

/**
 * Centralizes events dispatched for resources integration.
 *
 * @deprecated
 */
class ResourceLifecycleManager
{
    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var ObjectManager */
    private $om;

    public function __construct(
        StrictDispatcher $eventDispatcher,
        ObjectManager $om)
    {
        $this->dispatcher = $eventDispatcher;
        $this->om = $om;
    }

    public function load(ResourceNode $resourceNode)
    {
        /** @var LoadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('load', $resourceNode),
            LoadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function embed(ResourceNode $resourceNode)
    {
        /** @var EmbedResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('embed', $resourceNode),
            EmbedResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function create(ResourceNode $resourceNode)
    {
        /** @var CreateResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('create', $resourceNode),
            CreateResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function edit(ResourceNode $resourceNode)
    {
        // fixme : wrong event. Use crud instead ?

        /** @var CopyResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('edit', $resourceNode),
            CopyResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function copy($originalResource, $copiedResource)
    {
        /** @var CopyResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('copy', $copiedResource->getResourceNode()),
            CopyResourceEvent::class,
            [$originalResource, $copiedResource]
        );

        return $event;
    }

    public function export(ResourceNode $resourceNode)
    {
        /** @var DownloadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('export', $resourceNode),
            DownloadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function delete(ResourceNode $resourceNode, bool $soft = true)
    {
        /** @var DeleteResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('delete', $resourceNode),
            DeleteResourceEvent::class,
            [$this->getResourceFromNode($resourceNode), $soft]
        );

        return $event;
    }

    /**
     * Generates the names for dispatched events.
     */
    private static function eventName(string $prefix, ResourceNode $resourceNode): string
    {
        return 'resource.'.$resourceNode->getResourceType()->getName().'.'.$prefix;
    }

    /**
     * Returns the resource linked to a node.
     *
     * @return AbstractResource
     */
    private function getResourceFromNode(ResourceNode $resourceNode)
    {
        /** @var AbstractResource $resource */
        $resource = $this->om
            ->getRepository($resourceNode->getClass())
            ->findOneBy(['resourceNode' => $resourceNode]);

        return $resource;
    }
}
