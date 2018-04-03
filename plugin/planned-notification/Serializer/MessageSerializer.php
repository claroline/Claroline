<?php

namespace Claroline\PlannedNotificationBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\PlannedNotificationBundle\Entity\Message;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.planned_notification.message")
 * @DI\Tag("claroline.serializer")
 */
class MessageSerializer
{
    use SerializerTrait;

    private $workspaceRepo;

    /**
     * MessageSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
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
    public function deserialize($data, Message $message)
    {
        $message->setUuid($data['id']);

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
