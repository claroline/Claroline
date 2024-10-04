<?php

namespace Claroline\TagBundle\Subscriber;

use Claroline\AppBundle\Event\Crud\CopyEvent;
use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Event\Crud\DeleteEvent;
use Claroline\AppBundle\Event\CrudEvents;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TagBundle\Entity\TaggedObject;
use Claroline\TagBundle\Manager\TagManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class WorkspaceSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TagManager $manager
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CrudEvents::getEventName(CrudEvents::POST_CREATE, Workspace::class) => 'postCreate',
            CrudEvents::getEventName(CrudEvents::POST_COPY, Workspace::class) => 'postCopy',
            CrudEvents::getEventName(CrudEvents::PRE_DELETE, Workspace::class) => 'preDelete',
        ];
    }

    public function postCreate(CreateEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();
        $data = $event->getData();

        if (!empty($data['tags'])) {
            $this->manager->tagData($data['tags'], [[
                'class' => Workspace::class,
                'id' => $workspace->getUuid(),
                'name' => $workspace->getName(),
            ]]);
        }
    }

    public function postCopy(CopyEvent $event): void
    {
        /** @var Workspace $original */
        $original = $event->getObject();
        /** @var Workspace $copy */
        $copy = $event->getCopy();

        /** @var TaggedObject[] $taggedObjects */
        $taggedObjects = $this->manager->getTaggedObjects(Workspace::class, [$original->getUuid()]);
        if (!empty($taggedObjects)) {
            $tags = [];
            foreach ($taggedObjects as $taggedObject) {
                $tags[$taggedObject->getTag()->getId()] = $taggedObject->getTag()->getName();
            }

            $this->manager->tagData(array_values($tags), [[
                'class' => Workspace::class,
                'id' => $copy->getUuid(),
                'name' => $copy->getName(),
            ]]);
        }
    }

    public function preDelete(DeleteEvent $event): void
    {
        /** @var Workspace $workspace */
        $workspace = $event->getObject();

        $this->manager->removeTaggedObjectsByClassAndIds(Workspace::class, [$workspace->getId()]);
    }
}
