<?php

namespace Claroline\PeerTubeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\PeerTubeManager;

class VideoSerializer
{
    use SerializerTrait;

    /** @var PeerTubeManager */
    private $peerTubeManager;

    public function __construct(PeerTubeManager $peerTubeManager)
    {
        $this->peerTubeManager = $peerTubeManager;
    }

    public function getClass(): string
    {
        return Video::class;
    }

    public function serialize(Video $video, ?array $options = []): array
    {
        return [
            'id' => $video->getUuid(),
            'url' => $video->getUrl(),
            'embeddedUrl' => $video->getEmbeddedUrl(),
        ];
    }

    public function deserialize(array $data, Video $video, ?array $options = []): Video
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $video);
        } else {
            $video->refreshUuid();
        }

        if (!empty($data['url'])) {
            $urlParts = $this->peerTubeManager->extractUrlParts($data['url']);
            if (!empty($urlParts)) {
                $video->setServer($urlParts['server']);
                $video->setShortUuid($urlParts['shortUuid']);
                $video->setOriginalUuid(
                    $this->peerTubeManager->getVideoUuid($urlParts['server'], $urlParts['shortUuid'])
                );
            }
        }

        return $video;
    }
}
