<?php

namespace Claroline\SlideshowBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\SlideshowBundle\Entity\Resource\Slide;
use Claroline\SlideshowBundle\Entity\Resource\Slideshow;

class SlideshowSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /**
     * SlideshowSerializer constructor.
     */
    public function __construct(
        ObjectManager $om,
        PublicFileSerializer $fileSerializer
    ) {
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
    }

    public function getName()
    {
        return 'slideshow';
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return Slideshow::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/plugin/slideshow/slideshow.json';
    }

    /**
     * Serializes a Slideshow entity for the JSON api.
     *
     * @return array
     */
    public function serialize(Slideshow $slideshow)
    {
        return [
            'id' => $slideshow->getUuid(),
            'autoPlay' => $slideshow->getAutoPlay(),
            'interval' => $slideshow->getInterval(),
            'display' => [
                'description' => $slideshow->getDescription(),
                'showOverview' => $slideshow->getShowOverview(),
                'showControls' => $slideshow->getShowControls(),
            ],
            'slides' => array_map(function (Slide $slide) {
                $content = null;
                // TODO : enhance to allow more than files
                if (!empty($slide->getContent())) {
                    /** @var PublicFile $file */
                    $file = $this->om
                        ->getRepository(PublicFile::class)
                        ->findOneBy(['url' => $slide->getContent()]);

                    if ($file) {
                        $content = $this->fileSerializer->serialize($file);
                    }
                }

                return [
                    'id' => $slide->getUuid(),
                    'content' => $content,
                    'meta' => [
                        'title' => $slide->getTitle(),
                        'description' => $slide->getDescription(),
                    ],
                    'display' => [
                        'color' => $slide->getColor(),
                    ],
                ];
            }, $slideshow->getSlides()->toArray()),
        ];
    }

    /**
     * Deserializes Slideshow data into entities.
     *
     * @param array $data
     *
     * @return Slideshow
     */
    public function deserialize($data, Slideshow $slideshow, array $options = [])
    {
        $this->sipe('autoPlay', 'setAutoPlay', $data, $slideshow);
        $this->sipe('interval', 'setInterval', $data, $slideshow);
        $this->sipe('display.description', 'setDescription', $data, $slideshow);
        $this->sipe('display.showOverview', 'setShowOverview', $data, $slideshow);
        $this->sipe('display.showControls', 'setShowControls', $data, $slideshow);

        if (isset($data['slides'])) {
            // we will remove updated slides from this list
            // remaining ones will be deleted
            $existingSlides = $slideshow->getSlides()->toArray();

            foreach ($data['slides'] as $slideOrder => $slideData) {
                $slide = new Slide();

                /**
                 * check if slide already exists.
                 *
                 * @var int
                 * @var Slide $existing
                 */
                foreach ($existingSlides as $index => $existing) {
                    if (isset($existing) && $existing->getUuid() === $slideData['id']) {
                        // slide found
                        $slide = $existing;
                        unset($existingSlides[$index]);
                        break;
                    }
                }

                $slide->setOrder($slideOrder);

                if (!in_array(Options::REFRESH_UUID, $options)) {
                    $this->sipe('id', 'setUuid', $data, $slideshow);
                }

                $this->sipe('meta.title', 'setTitle', $slideData, $slide);
                $this->sipe('meta.description', 'setDescription', $slideData, $slide);
                $this->sipe('display.color', 'setColor', $slideData, $slide);

                // TODO : enhance to allow more than files (eg. HTML)
                $this->sipe('content.url', 'setContent', $slideData, $slide);

                $slideshow->addSlide($slide);
            }

            // Delete slides which no longer exist
            $slidesToDelete = array_values($existingSlides);
            foreach ($slidesToDelete as $toDelete) {
                $slideshow->removeSlide($toDelete);
            }
        }

        return $slideshow;
    }
}
