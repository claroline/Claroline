<?php

namespace Claroline\SchedulerBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Workspace\WorkspaceSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\SchedulerBundle\Entity\ScheduledTask;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ScheduledTaskSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var WorkspaceSerializer */
    private $workspaceSerializer;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        WorkspaceSerializer $workspaceSerializer,
        UserSerializer $userSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->workspaceSerializer = $workspaceSerializer;
        $this->userSerializer = $userSerializer;
    }

    public function getName()
    {
        return 'scheduled_task';
    }

    public function getSchema(): string
    {
        return '#/main/scheduler/scheduled-task.json';
    }

    /**
     * Serializes a ScheduledTask entity for the JSON api.
     *
     * @param ScheduledTask $scheduledTask - the task to serialize
     *
     * @return array - the serialized representation of the task
     */
    public function serialize(ScheduledTask $scheduledTask): array
    {
        return [
            'id' => $scheduledTask->getUuid(),
            'action' => $scheduledTask->getAction(),
            'name' => $scheduledTask->getName(),
            'executionType' => $scheduledTask->getExecutionType(),
            'scheduledDate' => DateNormalizer::normalize($scheduledTask->getScheduledDate()),
            'executionDate' => DateNormalizer::normalize($scheduledTask->getExecutionDate()),
            'endDate' => DateNormalizer::normalize($scheduledTask->getEndDate()),
            'status' => $scheduledTask->getStatus(),
            'parentId' => $scheduledTask->getParentId(),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $scheduledTask),
                'edit' => $this->authorization->isGranted('EDIT', $scheduledTask),
                'delete' => $this->authorization->isGranted('DELETE', $scheduledTask),
            ],
            'data' => $scheduledTask->getData(),
            'workspace' => $scheduledTask->getWorkspace() ? $this->workspaceSerializer->serialize($scheduledTask->getWorkspace(), [Options::SERIALIZE_MINIMAL]) : null,

            // TODO : to remove
            'users' => array_map(function (User $user) {
                return $this->userSerializer->serialize($user, [Options::SERIALIZE_MINIMAL]);
            }, $scheduledTask->getUsers()),
            'group' => $scheduledTask->getGroup() ? [
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
    public function deserialize(array $data, ScheduledTask $scheduledTask): ScheduledTask
    {
        $this->sipe('id', 'setUuid', $data, $scheduledTask);
        $this->sipe('name', 'setName', $data, $scheduledTask);
        $this->sipe('action', 'setAction', $data, $scheduledTask);
        $this->sipe('status', 'setStatus', $data, $scheduledTask);
        $this->sipe('parentId', 'setParentId', $data, $scheduledTask);
        $this->sipe('executionType', 'setExecutionType', $data, $scheduledTask);
        $this->sipe('executionInterval', 'setExecutionInterval', $data, $scheduledTask);

        $scheduledTask->setScheduledDate(DateNormalizer::denormalize($data['scheduledDate']));

        if (array_key_exists('executionDate', $data)) {
            $scheduledTask->setExecutionDate(DateNormalizer::denormalize($data['executionDate']));
        }
        if (array_key_exists('endDate', $data)) {
            $scheduledTask->setEndDate(DateNormalizer::denormalize($data['endDate']));
        }

        if (array_key_exists('data', $data)) {
            $scheduledTask->setData($data['data']);
        }

        // link Workspace
        if (array_key_exists('workspace', $data)) {
            $workspace = null;
            if (!empty($data['workspace'])) {
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy([
                    'uuid' => $data['workspace']['id'],
                ]);
            }

            $scheduledTask->setWorkspace($workspace);
        }

        // TODO : to remove
        // link Users
        $scheduledTask->emptyUsers(); // this is slightly brutal, but it's easier than checking if the users are already linked
        if (isset($data['users'])) {
            foreach ($data['users'] as $dataUser) {
                /** @var User $user */
                $user = $this->om->getRepository(User::class)->findOneBy([
                    'uuid' => $dataUser['id'],
                ]);

                if ($user) {
                    $scheduledTask->addUser($user);
                }
            }
        }

        // link Group
        if (isset($data['group'])) {
            if (empty($scheduledTask->getGroup()) || $data['group']['id'] !== $scheduledTask->getGroup()->getId()) {
                // Group has changed, we need to load the new one to link it to the entity
                $group = $this->om->getRepository(Group::class)->findOneBy([
                    'uuid' => $data['group']['id'],
                ]);

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
