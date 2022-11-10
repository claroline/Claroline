<?php

namespace Claroline\CoreBundle\API\Serializer\File;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\File\PublicFile;

/**
 * @todo move me in AppBundle
 */
class PublicFileSerializer
{
    /** @var ObjectManager */
    private $om;
    /** @var PlatformManager */
    private $platformManager;

    public function __construct(
        ObjectManager $om,
        PlatformManager $platformManager
    ) {
        $this->om = $om;
        $this->platformManager = $platformManager;
    }

    public function getClass(): string
    {
        return PublicFile::class;
    }

    public function getName(): string
    {
        return 'public_file';
    }

    public function getSchema(): string
    {
        return '#/main/core/publicFile.json';
    }

    public function serialize(PublicFile $file, array $options = []): array
    {
        $url = $file->getUrl();
        if (in_array(Options::ABSOLUTE_URL, $options)) {
            $url = $this->platformManager->getUrl().'/'.$url;
        }

        return [
            'id' => $file->getUuid(),
            'type' => $file->getMimeType(),
            'name' => $file->getFilename(),
            'size' => $file->getSize(),
            'url' => $url,

            // deprecated use `type` / `name` (this is to be compliant with the js File API)
            'mimeType' => $file->getMimeType(),
            'filename' => $file->getFilename(),
        ];
    }

    public function deserialize($data, PublicFile $file = null, array $options = []): ?PublicFile
    {
        // this is currently done in FileUtilities
        // todo : write correctly
        if (isset($data['id'])) {
            $file = $this->om->getObject($data, PublicFile::class);
        }

        return $file;
    }
}
