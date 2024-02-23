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

    public function getSchema(): string
    {
        return '#/integration/youtube/video.json';
    }

    public function serialize(Video $video): array
    {
        return [
            'id' => $video->getUuid(),
            'autoId' => $video->getId(),
            'videoId' => $video->getVideoId(),
            'url' => $video->getUrl(),
            'timecodeStart' => $video->getTimecodeStart(),
            'timecodeEnd' => $video->getTimecodeEnd(),
            'autoplay' => $video->getAutoplay(),
            'looping' => $video->getLooping(),
            'controls' => $video->getControls(),
            'resume' => $video->getResume(),
        ];
    }

    public function deserialize(array $data, Video $video): Video
    {
        parse_str(parse_url($data['url'], PHP_URL_QUERY), $params);
        $data['videoId'] = $params['v'];

        $this->sipe('videoId', 'setVideoId', $data, $video);
        $this->sipe('timecodeStart', 'setTimecodeStart', $data, $video);
        $this->sipe('timecodeEnd', 'setTimecodeEnd', $data, $video);
        $this->sipe('autoplay', 'setAutoplay', $data, $video);
        $this->sipe('looping', 'setLooping', $data, $video);
        $this->sipe('controls', 'setControls', $data, $video);
        $this->sipe('resume', 'setResume', $data, $video);

        return $video;
    }
}
