<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\MessageSerializer as AbstractMessageSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;

class MessageSerializer
{
    use SerializerTrait;

    /** @var AbstractMessageSerializer */
    private $messageSerializer;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceNodeSerializer */
    private $nodeSerializer;

    public function __construct(
        AbstractMessageSerializer $messageSerializer,
        ObjectManager $om,
        ResourceNodeSerializer $nodeSerializer
    ) {
        $this->messageSerializer = $messageSerializer;
        $this->om = $om;
        $this->nodeSerializer = $nodeSerializer;
    }

    public function getClass(): string
    {
        return Message::class;
    }

    public function getName(): string
    {
        return 'forum_message';
    }

    public function getSchema(): string
    {
        return '#/plugin/forum/message.json';
    }

    public function getSamples(): string
    {
        return '#/plugin/forum/message';
    }

    /**
     * Serializes a Message entity.
     */
    public function serialize(Message $message, ?array $options = []): array
    {
        $data = $this->messageSerializer->serialize($message, $options);
        $subject = $message->getSubject();

        if ($subject) {
            $data['subject'] = [
                'id' => $subject->getUuid(),
                'title' => $subject->getTitle(),
                'poster' => $subject->getPoster() ? $subject->getPoster()->getUrl() : null,
            ];

            if ($subject->getForum() && $subject->getForum()->getResourceNode()) {
                // required by the data source
                $data['meta']['resource'] = $this->nodeSerializer->serialize($subject->getForum()->getResourceNode(), [Options::SERIALIZE_MINIMAL]);
            }
        }

        $data['meta']['flagged'] = $message->isFlagged();
        $data['meta']['moderation'] = $message->getModerated();

        return $data;
    }

    /**
     * Deserializes data into a Message entity.
     */
    public function deserialize(array $data, Message $message, ?array $options = []): Message
    {
        $this->messageSerializer->deserialize($data, $message, $options);

        if (isset($data['subject'])) {
            /** @var Subject $subject */
            $subject = $this->om->getObject($data['subject'], Subject::class);

            if (!empty($subject)) {
                $message->setSubject($subject);
            }
        }

        if (isset($data['parent'])) {
            $parent = $this->om->getRepository($this->getClass())->findOneBy(['uuid' => $data['parent']['id']]);

            if ($parent) {
                $message->setParent($parent);
            }
        }
        $this->sipe('meta.flagged', 'setFlagged', $data, $message);
        $this->sipe('meta.moderation', 'setModerated', $data, $message);

        return $message;
    }
}
