<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\API\Serializer\SerializerTrait;

class TaskSerializer
{
    use SerializerTrait;

    public function getName()
    {
        return 'task';
    }

    public function serialize(Task $task): array
    {
        return [
            'id' => $task->getUuid(),
            'meta' => [
                'done' => $task->isDone(),
            ],
        ];
    }

    public function deserialize(array $data, Task $task): Task
    {
        $this->sipe('id', 'setUuid', $data, $task);
        $this->sipe('meta.done', 'setDone', $data, $task);

        return $task;
    }
}
