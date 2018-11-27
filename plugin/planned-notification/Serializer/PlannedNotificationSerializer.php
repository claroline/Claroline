<?php

namespace Claroline\PlannedNotificationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\PlannedNotificationBundle\Entity\PlannedNotification;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.planned_notification")
 * @DI\Tag("claroline.serializer")
 */
class PlannedNotificationSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    private $messageRepo;
    private $roleRepo;
    private $workspaceRepo;

    /**
     * PlannedNotificationSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->serializer = $serializer;

        $this->messageRepo = $om->getRepository('Claroline\PlannedNotificationBundle\Entity\Message');
        $this->roleRepo = $om->getRepository('Claroline\CoreBundle\Entity\Role');
        $this->workspaceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/planned-notification/planned-notification.json';
    }

    /**
     * @param PlannedNotification $notification
     *
     * @return array
     */
    public function serialize(PlannedNotification $notification)
    {
        return [
            'id' => $notification->getUuid(),
            'message' => $this->serializer->serialize($notification->getMessage()),
            'workspace' => $this->serializer->serialize($notification->getWorkspace(), [Options::SERIALIZE_MINIMAL]),
            'parameters' => [
                'action' => $notification->getAction(),
                'interval' => $notification->getInterval(),
                'byMail' => $notification->isByMail(),
                'byMessage' => $notification->isByMessage(),
            ],
            'roles' => array_map(function (Role $role) {
                return $this->serializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
            }, $notification->getRoles()->toArray()),
        ];
    }

    /**
     * @param array               $data
     * @param PlannedNotification $notification
     *
     * @return PlannedNotification
     */
    public function deserialize($data, PlannedNotification $notification)
    {
        $notification->setUuid($data['id']);

        $this->sipe('parameters.action', 'setAction', $data, $notification);
        $this->sipe('parameters.interval', 'setInterval', $data, $notification);
        $this->sipe('parameters.byMail', 'setByMail', $data, $notification);
        $this->sipe('parameters.byMessage', 'setByMessage', $data, $notification);

        if (isset($data['message']['id'])) {
            $message = $this->messageRepo->findOneBy(['uuid' => $data['message']['id']]);

            if (!empty($message)) {
                $notification->setMessage($message);
            }
        }
        if (isset($data['workspace']['uuid'])) {
            $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['uuid']]);

            if (!empty($workspace)) {
                $notification->setWorkspace($workspace);
            }
        }
        $notification->emptyRoles();

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $role = $this->roleRepo->findOneBy(['uuid' => $roleData['id']]);

                if (!empty($role)) {
                    $notification->addRole($role);
                }
            }
        }

        return $notification;
    }
}
