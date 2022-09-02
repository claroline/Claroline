<?php

namespace Claroline\TagBundle\Subscriber;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ResourceNodeSubscriber implements EventSubscriberInterface
{
    /** @var TagManager */
    private $manager;

    public function __construct(TagManager $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Crud::getEventName('create', 'post', ResourceNode::class) => 'postCreate',
            Crud::getEventName('copy', 'post', ResourceNode::class) => 'postCopy',
            Crud::getEventName('delete', 'pre', ResourceNode::class) => 'preDelete',
        ];
    }

    public function postCreate(CreateEvent $event)
    {
        /** @var ResourceNode $node */
        $node = $event->getObject();
        $data = $event->getData();

        if (!empty($data['tags'])) {
            $this->manager->tagData($data['tags'], [[
                'class' => ResourceNode::class,
                'id' => $node->getUuid(),
                'name' => $node->getName(),
            ]]);
        }
    }

    public function postCopy(CopyEvent $event)
    {
        /** @var ResourceNode $original */
        $original = $event->getObject();
        /** @var ResourceNode $copy */
        $copy = $event->getCopy();

        /** @var TaggedObject[] $taggedObjects */
        $taggedObjects = $this->manager->getTaggedObjects(ResourceNode::class, [$original->getUuid()]);
        if (!empty($taggedObjects)) {
            $tags = [];
            foreach ($taggedObjects as $taggedObject) {
                $tags[$taggedObject->getTag()->getId()] = $taggedObject->getTag()->getName();
            }

            $this->manager->tagData(array_values($tags), [[
                'class' => ResourceNode::class,
                'id' => $copy->getUuid(),
                'name' => $copy->getName(),
            ]]);
        }
    }

    public function preDelete(DeleteEvent $event)
    {
        /** @var ResourceNode $object */
        $object = $event->getObject();

        if (!in_array(Options::SOFT_DELETE, $event->getOptions())) {
            $this->manager->removeTaggedObjectsByClassAndIds(ResourceNode::class, [$object->getUuid()]);
        }
    }
}
