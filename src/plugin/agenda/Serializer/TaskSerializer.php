<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Planning\PlannedObjectSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TaskSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var PlannedObjectSerializer */
    private $plannedObjectSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        WorkspaceSerializer $workspaceSerializer,
        PlannedObjectSerializer $plannedObjectSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->plannedObjectSerializer = $plannedObjectSerializer;
    }

    public function getName()
    {
        return 'task';
    }

    public function getSchema()
    {
        return '#/plugin/agenda/task.json';
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

        if (isset($data['workspace'])) {
            $workspace = null;
            if (isset($data['workspace']['id'])) {
                /** @var Workspace $workspace */
                $workspace = $this->om->getObject($data['workspace'], Workspace::class);
            }

            $task->setWorkspace($workspace);
        }

        return $task;
    }
}
