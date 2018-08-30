<?php

namespace Claroline\CoreBundle\API\Serializer\Task;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Task\ScheduledTask;
use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.scheduled_task")
 * @DI\Tag("claroline.serializer")
 */
class ScheduledTaskSerializer
{
    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    /**
     * ScheduledTaskSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(
        ObjectManager  $om,
        SerializerProvider $serializer
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * Serializes a ScheduledTask entity for the JSON api.
     *
     * @param ScheduledTask $scheduledTask - the task to serialize
     *
     * @return array - the serialized representation of the task
     */
    public function serialize(ScheduledTask $scheduledTask)
    {
        return [
            'id' => $scheduledTask->getId(),
            'type' => $scheduledTask->getType(),
            'name' => $scheduledTask->getName(),
            'scheduledDate' => $scheduledTask->getScheduledDate()->format('Y-m-d\TH:i:s'),
            'data' => $scheduledTask->getData(),
            'meta' => [
                'lastExecution' => $scheduledTask->getExecutionDate() ? $scheduledTask->getExecutionDate()->format('Y-m-d\TH:i:s') : null,
            ],
            'users' => array_map(function (User $user) {
                return $this->serializer->serialize($user);
            }, $scheduledTask->getUsers()),
            'workspace' => $scheduledTask->getWorkspace() ? $this->serializer->serialize($scheduledTask->getWorkspace(), ['minimal']) : null,
            'group' => $scheduledTask->getGroup() ? [ // todo : use GroupSerializer when available
                'id' => $scheduledTask->getGroup()->getId(),
                'name' => $scheduledTask->getGroup()->getName(),
            ] : null,
        ];
    }

    /**
     * Deserializes JSON api data into a ScheduledTask entity.
     *
     * @param array         $data          - the data to deserialize
     * @param ScheduledTask $scheduledTask - the task entity to update
     *
     * @return ScheduledTask - the updated task entity
     */
    public function deserialize(array $data, ScheduledTask $scheduledTask = null)
    {
        $scheduledTask = $scheduledTask ?: new ScheduledTask();

        $scheduledTask->setName($data['name']);
        $scheduledTask->setType($data['type']);

        $scheduledDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $data['scheduledDate']);
        $scheduledTask->setScheduledDate($scheduledDate);

        if (isset($data['data'])) {
            $scheduledTask->setData($data['data']);
        }

        // link Workspace
        if (isset($data['workspace'])) {
            if (empty($scheduledTask->getWorkspace()) || $data['workspace']['id'] !== $scheduledTask->getWorkspace()->getId()) {
                // WS has changed, we need to load the new one to link it to the entity
                $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->find(
                    $data['workspace']['id']
                );

                if ($workspace) {
                    $scheduledTask->setWorkspace($workspace);
                }
            }
        } else {
            $scheduledTask->setWorkspace(null);
        }

        // link Users
        $scheduledTask->emptyUsers(); // this is slightly brutal, but it's easier than checking if the users are already linked
        if (isset($data['users'])) {
            foreach ($data['users'] as $dataUser) {
                /** @var User $user */
                $user = $this->om->getRepository('ClarolineCoreBundle:User')->find(
                    $dataUser['id']
                );

                if ($user) {
                    $scheduledTask->addUser($user);
                }
            }
        }

        // link Group
        if (isset($data['group'])) {
            if (empty($scheduledTask->getGroup()) || $data['group']['id'] !== $scheduledTask->getGroup()->getId()) {
                // Group has changed, we need to load the new one to link it to the entity
                $group = $this->om->getRepository('ClarolineCoreBundle:Group')->find(
                    $data['group']['id']
                );

                if ($group) {
                    $scheduledTask->setGroup($group);
                }
            }
        } else {
            $scheduledTask->setGroup(null);
        }

        return $scheduledTask;
    }
}
