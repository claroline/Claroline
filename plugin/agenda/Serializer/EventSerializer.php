<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;

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
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function serialize(Event $event)
    {
        return [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $event->getStart() ? DateNormalizer::normalize($event->getStart()) : null,
            'end' => $event->getEnd() ? DateNormalizer::normalize($event->getEnd()) : null,
            'color' => $event->getPriority(),
            'allDay' => $event->isAllDay(),
            'durationEditable' => !$event->isTask() && false !== $event->isEditable(),
            'owner' => $this->serializer->serialize($event->getUser()),
            'description' => $event->getDescription(),
            'workspace' => $event->getWorkspace() ? $this->serializer->serialize($event->getWorkspace()) : null,
            'className' => 'event_'.$event->getId(),
            'editable' => false !== $event->isEditable(),
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
            $workspace = $this->serializer->deserialize('Claroline\CoreBundle\Entity\Workspace\Workspace', $data['workspace']);
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
        return 'Claroline\AgendaBundle\Entity\Event';
    }
}
