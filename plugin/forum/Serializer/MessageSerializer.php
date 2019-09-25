<?php

namespace Claroline\ForumBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\MessageSerializer as AbstractMessageSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\ForumBundle\Entity\Message;
use Claroline\ForumBundle\Entity\Subject;

class MessageSerializer
{
    use SerializerTrait;

    /** @var AbstractMessageSerializer */
    private $messageSerializer;

    /** @var AbstractMessageSerializer */
    private $subjectSerializer;

    /** @var ObjectManager */
    private $om;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /** @var ResourceNodeSerializer */
    private $nodeSerializer;

    /**
     * MessageSerializer constructor.
     *
     * @param AbstractMessageSerializer $messageSerializer
     * @param ObjectManager             $om
     * @param SubjectSerializer         $subjectSerializer
     * @param PublicFileSerializer      $fileSerializer
     * @param ResourceNodeSerializer    $nodeSerializer
     */
    public function __construct(
        AbstractMessageSerializer $messageSerializer,
        ObjectManager $om,
        SubjectSerializer $subjectSerializer,
        PublicFileSerializer $fileSerializer,
        ResourceNodeSerializer $nodeSerializer
    ) {
        $this->messageSerializer = $messageSerializer;
        $this->om = $om;
        $this->subjectSerializer = $subjectSerializer;
        $this->fileSerializer = $fileSerializer;
        $this->nodeSerializer = $nodeSerializer;
    }

    public function getClass()
    {
        return Message::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/forum/message.json';
    }

    /**
     * @return string
     */
    public function getSamples()
    {
        return '#/plugin/forum/message';
    }

    /**
     * Serializes a Message entity.
     *
     * @param Message $message
     * @param array   $options
     *
     * @return array
     */
    public function serialize(Message $message, array $options = [])
    {
        $data = $this->messageSerializer->serialize($message, $options);
        $subject = $message->getSubject();

        if ($subject) {
            $data['subject'] = [
                'id' => $subject->getUuid(),
                'title' => $subject->getTitle(),
            ];

            if ($subject->getForum() && $subject->getForum()->getResourceNode()) {
                // required by the data source
                $data['meta']['resource'] = $this->nodeSerializer->serialize($subject->getForum()->getResourceNode(), [Options::SERIALIZE_MINIMAL]);
            }

            $data['meta']['poster'] = $subject->getPoster() ?
              $this->fileSerializer->serialize($subject->getPoster()) :
              null;
        }

        $data['meta']['flagged'] = $message->isFlagged();
        $data['meta']['moderation'] = $message->getModerated();

        return $data;
    }

    /**
     * Deserializes data into a Message entity.
     *
     * @param array   $data
     * @param Message $message
     * @param array   $options
     *
     * @return Message
     */
    public function deserialize($data, Message $message, array $options = [])
    {
        $message = $this->messageSerializer->deserialize($data, $message, $options);

        if (isset($data['subject'])) {
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
