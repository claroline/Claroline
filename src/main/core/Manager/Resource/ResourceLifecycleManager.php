<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
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
    public function __construct(
        private readonly StrictDispatcher $dispatcher,
        private readonly ObjectManager $om
    ) {
    }

    public function load(ResourceNode $resourceNode): LoadResourceEvent
    {
        /** @var LoadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('load', $resourceNode),
            LoadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function embed(ResourceNode $resourceNode): EmbedResourceEvent
    {
        /** @var EmbedResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('embed', $resourceNode),
            EmbedResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function copy($originalResource, $copiedResource): CopyResourceEvent
    {
        /** @var CopyResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('copy', $copiedResource->getResourceNode()),
            CopyResourceEvent::class,
            [$originalResource, $copiedResource]
        );

        return $event;
    }

    public function export(ResourceNode $resourceNode): DownloadResourceEvent
    {
        /** @var DownloadResourceEvent $event */
        $event = $this->dispatcher->dispatch(
            static::eventName('export', $resourceNode),
            DownloadResourceEvent::class,
            [$this->getResourceFromNode($resourceNode)]
        );

        return $event;
    }

    public function delete(ResourceNode $resourceNode, bool $soft = true): DeleteResourceEvent
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
    private static function eventName(string $action, ResourceNode $resourceNode): string
    {
        return ResourceEvents::getEventName($action, $resourceNode->getResourceType()->getName());
    }

    /**
     * Returns the resource linked to a node.
     */
    private function getResourceFromNode(ResourceNode $resourceNode): ?AbstractResource
    {
        /** @var AbstractResource $resource */
        $resource = $this->om
            ->getRepository($resourceNode->getClass())
            ->findOneBy(['resourceNode' => $resourceNode]);

        return $resource;
    }
}
