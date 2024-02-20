<?php

namespace Claroline\PeerTubeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\PeerTubeBundle\Entity\Video;
use Claroline\PeerTubeBundle\Manager\PeerTubeManager;

class VideoSerializer
{
    use SerializerTrait;

    private PeerTubeManager $peerTubeManager;

    public function __construct(PeerTubeManager $peerTubeManager)
    {
        $this->peerTubeManager = $peerTubeManager;
    }

    public function getClass(): string
    {
        return Video::class;
    }

    public function getSchema(): string
    {
        return '#/integration/peertube/video.json';
    }

    public function serialize(Video $video): array
    {
        return [
            'id' => $video->getUuid(),
            'url' => $video->getUrl(),
            'embeddedUrl' => $video->getEmbeddedUrl(),
            'timecodeStart' => $video->getTimecodeStart(),
            'timecodeEnd' => $video->getTimecodeEnd(),
            'autoplay' => $video->getAutoplay(),
            'looping' => $video->getLooping(),
            'controls' => $video->getControls(),
            'peertubeLink' => $video->getPeertubeLink(),
        ];
    }

    public function deserialize(array $data, Video $video, ?array $options = []): Video
    {
        if (!in_array(SerializerInterface::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $video);
            $this->sipe('timecodeStart', 'setTimecodeStart', $data, $video);
            $this->sipe('timecodeEnd', 'setTimecodeEnd', $data, $video);
            $this->sipe('autoplay', 'setAutoplay', $data, $video);
            $this->sipe('looping', 'setLooping', $data, $video);
            $this->sipe('controls', 'setControls', $data, $video);
            $this->sipe('peertubeLink', 'setPeertubeLink', $data, $video);
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
