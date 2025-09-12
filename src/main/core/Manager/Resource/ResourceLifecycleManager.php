<?php

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\CatalogEvents\ResourceEvents;
use Claroline\CoreBundle\Event\Resource\CopyResourceEvent;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
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
        $this->eventDispatcher->dispatch($event, ResourceEvents::getEventName(ResourceEvents::EMBED, $resourceNode->getType()));

        return $event;
    }

    public function copy(AbstractResource $originalResource, AbstractResource $copiedResource): CopyResourceEvent
    {
        $event = new CopyResourceEvent($originalResource, $copiedResource);
        $this->eventDispatcher->dispatch($event, ResourceEvents::getEventName(ResourceEvents::COPY, $copiedResource->getResourceNode()->getType()));

        return $event;
    }

    public function delete(ResourceNode $resourceNode, bool $soft = true): DeleteResourceEvent
    {
        $event = new DeleteResourceEvent($this->getResourceFromNode($resourceNode), $soft);
        $this->eventDispatcher->dispatch($event, ResourceEvents::getEventName(ResourceEvents::DELETE, $resourceNode->getType()));

        return $event;
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
