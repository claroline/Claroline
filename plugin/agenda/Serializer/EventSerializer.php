<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @DI\Service("claroline.serializer.event")
 * @DI\Tag("claroline.serializer")
 */
class EventSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * RoleSerializer constructor.
     *
     * @DI\InjectParams({
     *     "workspaceSerializer" = @DI\Inject("claroline.serializer.workspace"),
     *     "userSerializer"      = @DI\Inject("claroline.serializer.user"),
     *     "authorization"       = @DI\Inject("security.authorization_checker")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(
      AuthorizationCheckerInterface $authorization,
      WorkspaceSerializer $workspaceSerializer,
      UserSerializer $userSerializer
    ) {
        $this->authorization = $authorization;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function serialize(Event $event)
    {
        $editable = $event->getWorkspace() ?
            $this->authorization->isGranted('EDIT', $event) :
            false !== $event->isEditable();

        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $event->getStart() ? DateNormalizer::normalize($event->getStart()) : null,
            'end' => $event->getEnd() ? DateNormalizer::normalize($event->getEnd()) : null,
            'color' => $event->getPriority(),
            'allDay' => $event->isAllDay(),
            'durationEditable' => !$event->isTask() && false !== $event->isEditable(),
            'owner' => $this->userSerializer->serialize($event->getUser()),
            'description' => $event->getDescription(),
            'workspace' => $event->getWorkspace() ? $this->workspaceSerializer->serialize($event->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null,
            'className' => 'event_'.$event->getId(),
            'editable' => $editable,
            'meta' => $this->serializeMeta($event),
        ];
    }

    public function serializeMeta(Event $event)
    {
        return [
          'task' => $event->isTask(),
          'isTaskDone' => $event->isTaskDone(),
        ];
    }

    /**
     * @param array      $data
     * @param Event|null $event
     *
     * @return Event
     */
    public function deserialize(array $data, Event $event = null)
    {
        $this->sipe('title', 'setTitle', $data, $event);
        $this->sipe('color', 'setPriority', $data, $event);
        $this->sipe('allDay', 'setAllDay', $data, $event);
        $this->sipe('description', 'setDescription', $data, $event);
        $this->sipe('meta.task', 'setIsTask', $data, $event);
        $this->sipe('isTaskDone', 'setIsTaskDone', $data, $event);
        $this->sipe('isEditable', 'setIsEditable', $data, $event);

        if (isset($data['workspace'])) {
            $workspace = $this->_om->getObject($data['workspace'], Workspace::class);
            if ($workspace->getId()) {
                $event->setWorkspace($workspace);
            }
        }
        //owner set in crud create

        if (isset($data['start'])) {
            $event->setStart(DateNormalizer::denormalize($data['start']));
        }

        if (isset($data['end'])) {
            $event->setEnd(DateNormalizer::denormalize($data['end']));
        }

        return $event;
    }

    public function getClass()
    {
        return Event::class;
    }
}
