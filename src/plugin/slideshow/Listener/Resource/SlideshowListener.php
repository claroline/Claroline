<?php

namespace Claroline\SlideshowBundle\Listener\Resource;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\SlideshowBundle\Entity\Resource\Slideshow;

/**
 * Used to integrate Slideshow to Claroline resource manager.
 */
class SlideshowListener extends ResourceComponent
{
    public function __construct(
        private readonly SerializerProvider $serializer,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getName(): string
    {
        return 'claro_slideshow';
    }

    /** @var Slideshow $resource */
    public function open(AbstractResource $resource, bool $embedded = false): ?array
    {
        return [
            'slideshow' => $this->serializer->serialize($resource),
        ];
    }

    /** @var Slideshow $resource */
    public function delete(AbstractResource $resource, FileBag $fileBag, bool $softDelete = true): bool
    {
        $files = [];
        if ($softDelete) {
            return true;
        }

        foreach ($resource->getSlides() as $slide) {
            if (!empty($slide->getContent())) {
                // for now all slides are files
                $fileBag->add($slide->getContent(), $this->fileManager->getDirectory().DIRECTORY_SEPARATOR.$slide->getContent());
            }
        }

        return true;
    }
}
