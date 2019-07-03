<?php

namespace Claroline\CoreBundle\API\Serializer\ConnectionMessage;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\ConnectionMessage\Slide;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.connection.message.slide")
 * @DI\Tag("claroline.serializer")
 */
class SlideSerializer
{
    use SerializerTrait;

    private $messageRepo;

    /**
     * SlideSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->messageRepo = $om->getRepository(ConnectionMessage::class);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Slide::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/connection-message/slide.json';
    }

    /**
     * Serializes a Slide entity for the JSON api.
     *
     * @param Slide $slide
     *
     * @return array
     */
    public function serialize(Slide $slide)
    {
        return [
            'id' => $slide->getUuid(),
            'title' => $slide->getTitle(),
            'content' => $slide->getContent(),
            'poster' => $slide->getPoster() ? [
                'url' => $slide->getPoster(),
                'mimeType' => 'image/*',
            ] : null,
            'order' => $slide->getOrder(),
        ];
    }

    /**
     * Deserializes Slide data into entities.
     *
     * @param array $data
     * @param Slide $slide
     *
     * @return Slide
     */
    public function deserialize($data, Slide $slide)
    {
        $this->sipe('id', 'setUuid', $data, $slide);
        $this->sipe('title', 'setTitle', $data, $slide);
        $this->sipe('content', 'setContent', $data, $slide);
        $this->sipe('poster.url', 'setPoster', $data, $slide);
        $this->sipe('order', 'setOrder', $data, $slide);

        if (isset($data['message']['id']) && !$slide->getMessage()) {
            $message = $this->messageRepo->findOneBy(['uuid' => $data['message']['id']]);

            if ($message) {
                $slide->setMessage($message);
            }
        }

        return $slide;
    }
}
