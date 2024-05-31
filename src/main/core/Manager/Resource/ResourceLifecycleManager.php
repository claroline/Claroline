<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\CreateResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\DownloadResourceEvent;
use Claroline\CoreBundle\Event\Resource\EmbedResourceEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Centralizes events dispatched for resources integration.
 *
 * @deprecated
 */
class ResourceLifecycleManager
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ObjectManager $om
    ) {
    }

    public function embed(ResourceNode $resourceNode): EmbedResourceEvent
    {
        $event = new EmbedResourceEvent($this->getResourceFromNode($resourceNode));
        $this->eventDispatcher->dispatch($event, static::eventName('embed', $resourceNode));

        return $event;
    }

    public function create(ResourceNode $resourceNode): CreateResourceEvent
    {
        $event = new CreateResourceEvent($this->getResourceFromNode($resourceNode));
        $this->eventDispatcher->dispatch($event, static::eventName('create', $resourceNode));

        return $event;
    }

    public function copy($originalResource, $copiedResource): CopyResourceEvent
    {
        $event = new CopyResourceEvent($originalResource, $copiedResource);
        $this->eventDispatcher->dispatch($event, static::eventName('copy', $copiedResource->getResourceNode()));

        return $event;
    }

    public function export(ResourceNode $resourceNode): DownloadResourceEvent
    {
        $event = new DownloadResourceEvent($this->getResourceFromNode($resourceNode));
        $this->eventDispatcher->dispatch($event, static::eventName('export', $resourceNode));

        return $event;
    }

    public function delete(ResourceNode $resourceNode, bool $soft = true): DeleteResourceEvent
    {
        $event = new DeleteResourceEvent($this->getResourceFromNode($resourceNode), $soft);
        $this->eventDispatcher->dispatch($event, static::eventName('delete', $resourceNode));

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
    private function getResourceFromNode(ResourceNode $resourceNode): ?AbstractResource
    {
        /** @var AbstractResource $resource */
        $resource = $this->om
            ->getRepository($resourceNode->getClass())
            ->findOneBy(['resourceNode' => $resourceNode]);

        return $resource;
    }
}
