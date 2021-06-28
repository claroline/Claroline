<?php

namespace Claroline\TagBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Manager\TagManager;

class ResourceListener
{
    /** @var TagManager */
    private $manager;

    /**
     * ResourceListener constructor.
     */
    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    public function onDelete(GenericDataEvent $event)
    {
        /** @var ResourceNode[] $resources */
        $resources = $event->getData();

        $ids = [];
        foreach ($resources as $resource) {
            $ids[] = $resource->getId();
        }

        $this->manager->removeTaggedObjectsByClassAndIds(ResourceNode::class, $ids);
    }
}
