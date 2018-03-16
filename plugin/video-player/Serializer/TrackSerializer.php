<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\VideoPlayerBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\VideoPlayerBundle\Entity\Track;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.video.track")
 * @DI\Tag("claroline.serializer")
 */
class TrackSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var FileManager */
    private $fileManager;

    private $fileRepo;
    private $trackRepo;

    /**
     * PathSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "fileManager" = @DI\Inject("claroline.manager.file_manager")
     * })
     *
     * @param ObjectManager $om
     * @param FileManager   $fileManager
     */
    public function __construct(ObjectManager $om, FileManager $fileManager)
    {
        $this->om = $om;
        $this->fileManager = $fileManager;
        $this->fileRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\File');
        $this->trackRepo = $om->getRepository('Claroline\VideoPlayerBundle\Entity\Track');
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/video-player/track.json';
    }

    /**
     * @param Track $track
     *
     * @return array
     */
    public function serialize(Track $track)
    {
        return [
            'id' => $track->getUuid(),
            'autoId' => $track->getId(),
            'video' => [
                'id' => $track->getVideo()->getId(),
            ],
            'meta' => [
                'label' => $track->getLabel(),
                'lang' => $track->getLang(),
                'kind' => $track->getKind(),
                'default' => $track->isDefault(),
            ],
        ];
    }

    /**
     * Deserializes data into a Track entity.
     *
     * @param \stdClass $data
     * @param Track     $track
     *
     * @return Track
     */
    public function deserialize($data, Track $track)
    {
        if (empty($track)) {
            $track = new Track();
        }
        $track->setUuid($data['id']);
        $video = $this->fileRepo->findOneBy(['id' => $data['video']['id']]);
        $track->setVideo($video);
        $this->sipe('meta.label', 'setLabel', $data, $track);
        $this->sipe('meta.lang', 'setLang', $data, $track);
        $this->sipe('meta.kind', 'setKind', $data, $track);
        $this->sipe('meta.default', 'setIsDefault', $data, $track);

        if (isset($data['file'])) {
            $trackFile = $this->fileManager->create(
                new File(),
                $data['file'],
                $data['file']->getClientOriginalName(),
                $data['file']->getMimeType(),
                $video->getResourceNode()->getWorkspace()
            );
            $this->om->persist($trackFile);
            $track->setTrackFile($trackFile);
        }

        return $track;
    }
}
