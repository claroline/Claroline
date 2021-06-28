<?php

namespace Claroline\CoreBundle\API\Serializer\File;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;

/**
 * @todo move me in AppBundle
 */
class PublicFileSerializer
{
    /** @var PlatformManager */
    private $platformManager;
    /** @var FileUtilities */
    private $utilities;

    public function __construct(
        PlatformManager $platformManager,
        FileUtilities $utilities
    ) {
        $this->platformManager = $platformManager;
        $this->utilities = $utilities;
    }

    public function getClass(): string
    {
        return PublicFile::class;
    }

    public function getName()
    {
        return 'public_file';
    }

    public function getSchema()
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
            'id' => $file->getId(),
            'type' => $file->getMimeType(),
            'name' => $file->getFilename(),
            'size' => $file->getSize(),
            'directory' => $file->getDirectoryName(), // I'm not sure this is needed
            'sourceType' => $file->getSourceType(),
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
            return $this->utilities->getOneBy(['id' => $data['id']]);
        }

        return null;
    }
}
