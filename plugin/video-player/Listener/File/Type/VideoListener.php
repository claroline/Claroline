<?php

namespace Claroline\VideoPlayerBundle\Listener\File\Type;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Event\Resource\File\LoadFileEvent;
use Claroline\VideoPlayerBundle\Entity\Track;
use Claroline\VideoPlayerBundle\Manager\VideoPlayerManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 *
 * @todo : manage audio in it's own plugin
 */
class VideoListener
{
    /** @var SerializerProvider */
    private $serializer;

    /** @var VideoPlayerManager */
    private $manager;

    /**
     * VideoListener constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "manager"    = @DI\Inject("claroline.manager.video_player_manager")
     * })
     *
     * @param SerializerProvider $serializer
     * @param VideoPlayerManager $manager
     */
    public function __construct(
        SerializerProvider $serializer,
        VideoPlayerManager $manager)
    {
        $this->serializer = $serializer;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("file.audio.load")
     * @DI\Observe("file.video.load")
     *
     * @param LoadFileEvent $event
     */
    public function onLoad(LoadFileEvent $event)
    {
        /** @var File $resource */
        $resource = $event->getResource();
        $tracks = $this->manager->getTracksByVideo($resource);

        $event->setData([
            'tracks' => array_map(function (Track $track) {
                return $this->serializer->serialize($track);
            }, $tracks),
        ]);
        $event->stopPropagation();
    }
}
