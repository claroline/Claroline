<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\Planning\PlannedObjectSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var PlannedObjectSerializer */
    private $plannedObjectSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        WorkspaceSerializer $workspaceSerializer,
        PlannedObjectSerializer $plannedObjectSerializer
    ) {
        $this->authorization = $authorization;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->plannedObjectSerializer = $plannedObjectSerializer;
    }

    public function getName()
    {
        return 'event';
    }

    public function serialize(Event $event, array $options = []): array
    {
        return array_merge_recursive($this->plannedObjectSerializer->serialize($event->getPlannedObject(), $options), [
            'workspace' => $event->getWorkspace() ? $this->workspaceSerializer->serialize($event->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null,
            'permissions' => [
                'edit' => $this->authorization->isGranted('EDIT', $event),
                'delete' => $this->authorization->isGranted('DELETE', $event),
            ],
        ]);
    }

    public function deserialize(array $data, Event $event): Event
    {
        $this->plannedObjectSerializer->deserialize($data, $event->getPlannedObject());

        $this->sipe('id', 'setUuid', $data, $event);

        return $event;
    }
}
