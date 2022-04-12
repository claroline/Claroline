<?php

namespace Claroline\SlideshowBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Event\Resource\DeleteResourceEvent;
use Claroline\CoreBundle\Event\Resource\ExportResourceEvent;
use Claroline\CoreBundle\Event\Resource\ImportResourceEvent;
use Claroline\CoreBundle\Event\Resource\LoadResourceEvent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\SlideshowBundle\Entity\Resource\Slideshow;

/**
 * Used to integrate Slideshow to Claroline resource manager.
 */
class SlideshowListener
{
    /** @var SerializerProvider */
    private $serializer;
    /** @var FileManager */
    private $fileManager;

    public function __construct(
        SerializerProvider $serializer,
        FileManager $fileManager
    ) {
        $this->serializer = $serializer;
        $this->fileManager = $fileManager;
    }

    /**
     * Loads the Slideshow resource.
     */
    public function onLoad(LoadResourceEvent $event)
    {
        /** @var Slideshow $slideshow */
        $slideshow = $event->getResource();

        $event->setData([
            'slideshow' => $this->serializer->serialize($slideshow),
        ]);
        $event->stopPropagation();
    }

    /**
     * Deletes Slideshow files when the resource is deleted.
     */
    public function onDelete(DeleteResourceEvent $event)
    {
        /** @var Slideshow $slideshow */
        $slideshow = $event->getResource();

        $files = [];
        foreach ($slideshow->getSlides() as $slide) {
            if (!$slide->getContent()) {
                // for now all slides are files
                $files[] = $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$slide->getContent();
            }
        }

        $event->setFiles($files);
        $event->stopPropagation();
    }

    public function onExport(ExportResourceEvent $event)
    {
        // TODO : implement. It should export Slides files
    }

    public function onImport(ImportResourceEvent $event)
    {
        // TODO : implement. It should import Slides files
    }
}
