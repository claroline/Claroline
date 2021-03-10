<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\Planning\PlannedObjectSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TaskSerializer
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
        return 'task';
    }

    public function serialize(Task $task, array $options = []): array
    {
        return array_merge_recursive($this->plannedObjectSerializer->serialize($task->getPlannedObject(), $options), [
            'workspace' => $task->getWorkspace() ? $this->workspaceSerializer->serialize($task->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null,
            'meta' => [
                'done' => $task->isDone(),
            ],
            'permissions' => [
                'edit' => $this->authorization->isGranted('EDIT', $task),
                'delete' => $this->authorization->isGranted('DELETE', $task),
            ],
        ]);
    }

    public function deserialize(array $data, Task $task): Task
    {
        $this->plannedObjectSerializer->deserialize($data, $task->getPlannedObject());

        $this->sipe('id', 'setUuid', $data, $task);
        $this->sipe('meta.done', 'setDone', $data, $task);

        return $task;
    }
}
