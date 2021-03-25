<?php

namespace Claroline\PlannedNotificationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\PlannedNotificationBundle\Entity\Message;

class MessageSerializer
{
    use SerializerTrait;

    private $workspaceRepo;

    /**
     * MessageSerializer constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->workspaceRepo = $om->getRepository('Claroline\CoreBundle\Entity\Workspace\Workspace');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/planned-notification/message.json';
    }

    public function getName()
    {
        return 'planned_notification_message';
    }

    /**
     * @return array
     */
    public function serialize(Message $message)
    {
        return [
            'id' => $message->getUuid(),
            'title' => $message->getTitle(),
            'content' => $message->getContent(),
        ];
    }

    /**
     * @param array $data
     *
     * @return Message
     */
    public function deserialize($data, Message $message, array $options)
    {
        if (!in_array(Options::REFRESH_UUID, $options)) {
            $message->setUuid($data['id']);
        }

        $this->sipe('title', 'setTitle', $data, $message);
        $this->sipe('content', 'setContent', $data, $message);

        if (isset($data['workspace']['id'])) {
            /** @var Workspace $workspace */
            $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['id']]);

            if (!empty($workspace)) {
                $message->setWorkspace($workspace);
            }
        }

        return $message;
    }
}
