<?php

namespace Claroline\CoreBundle\Subscriber;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\GlobalSearchEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GlobalSearchSubscriber implements EventSubscriberInterface
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GlobalSearchEvent::class => 'search',
        ];
    }

    public function search(GlobalSearchEvent $event)
    {
        $search = $event->getSearch();
        $limit = $event->getLimit();

        if ($event->includeItems('user')) {
            $users = $this->om->getRepository(User::class)->search($search, $limit);

            $event->addResults('user', array_map(function (User $user) {
                return $this->serializer->serialize($user, [Options::SERIALIZE_MINIMAL]);
            }, $users));
        }

        if ($event->includeItems('workspace')) {
            $workspaces = $this->om->getRepository(Workspace::class)->search($search, $limit);

            $event->addResults('workspace', array_map(function (Workspace $workspace) {
                return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }, $workspaces));
        }

        if ($event->includeItems('resource')) {
            $resources = $this->om->getRepository(ResourceNode::class)->search($search, $limit);

            $event->addResults('resource', array_map(function (ResourceNode $resource) {
                return $this->serializer->serialize($resource, [Options::SERIALIZE_MINIMAL]);
            }, $resources));
        }
    }
}
