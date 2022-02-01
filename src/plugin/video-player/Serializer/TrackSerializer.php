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
use Claroline\VideoPlayerBundle\Entity\Track;

class TrackSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    private $fileRepo;
    private $trackRepo;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
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

    public function getName()
    {
        return 'video_track';
    }

    public function serialize(Track $track): array
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
     */
    public function deserialize(array $data, Track $track): Track
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

        return $track;
    }
}
