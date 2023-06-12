<?php

namespace Claroline\YouTubeBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\YouTubeBundle\Entity\Video;

class VideoSerializer
{
    use SerializerTrait;

    public function getClass(): string
    {
        return Video::class;
    }

    public function serialize(Video $video): array
    {
        return [
            'id' => $video->getUuid(),
            'autoId' => $video->getId(),
            'videoId' => $video->getVideoId(),
            'url' => $video->getUrl(),
        ];
    }

    public function deserialize(array $data, Video $video): Video
    {
        parse_str(parse_url($data['url'], PHP_URL_QUERY), $params);
        $data['videoId'] = $params['v'];

        $this->sipe('videoId', 'setVideoId', $data, $video);

        return $video;
    }
}
