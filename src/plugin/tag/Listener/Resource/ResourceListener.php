<?php

namespace Claroline\TagBundle\Listener\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\TagBundle\Manager\TagManager;

class ResourceListener
{
    /** @var TagManager */
    private $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    public function onDelete(DeleteEvent $event)
    {
        /** @var ResourceNode $object */
        $object = $event->getObject();

        if (!in_array(Options::SOFT_DELETE, $event->getOptions())) {
            $this->manager->removeTaggedObjectsByClassAndIds(ResourceNode::class, [$object->getUuid()]);
        }
    }
}
