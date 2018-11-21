<?php

namespace Claroline\TagBundle\Listener\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\GenericDataEvent;
use Claroline\TagBundle\Manager\TagManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ResourceListener
{
    /** @var TagManager */
    private $manager;

    /**
     * ResourceListener constructor.
     *
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("claroline.manager.tag_manager")
     * })
     *
     * @param TagManager $manager
     */
    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("claroline_resources_delete")
     *
     * @param GenericDataEvent $event
     */
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
