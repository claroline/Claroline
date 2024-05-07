<?php

namespace Claroline\CoreBundle\Component\Resource;

use Claroline\AppBundle\API\Utils\FileBag;
use Claroline\AppBundle\Component\ComponentInterface;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;

/**
 * Implement this interface in your ResourceComponent in order to make it downloadable.
 */
interface DownloadableResourceInterface
{
    /**
     * Download a "stand alone" version of the resource (most likely a PDF).
     * It returns the path to the generated file to download.
     *
     * NB. Not all resources types are able to create a downloadable version.
     */
    public function download(AbstractResource $resource): ?string;
}
