<?php

namespace Claroline\PlannedNotificationBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\PlannedNotificationBundle\Entity\Message;

class MessageSerializer
{
    use SerializerTrait;

    private $workspaceRepo;

    /**
     * MessageSerializer constructor.
     *
     * @param ObjectManager $om
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
     * @param Message $message
     *
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
     * @param array   $data
     * @param Message $message
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

        if (isset($data['workspace']['uuid'])) {
            $workspace = $this->workspaceRepo->findOneBy(['uuid' => $data['workspace']['uuid']]);

            if (!empty($workspace)) {
                $message->setWorkspace($workspace);
            }
        }

        return $message;
    }
}
