<?php

namespace Claroline\CoreBundle\API\Serializer\File;

use Claroline\CoreBundle\Entity\File\PublicFile;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.public_file")
 * @DI\Tag("claroline.serializer")
 *
 * @todo move me in AppBundle
 */
class PublicFileSerializer
{
    private $utilities;

    /**
     * @DI\InjectParams({
     *     "utilities" = @DI\Inject("claroline.utilities.file")
     * })
     */
    public function __construct($utilities)
    {
        $this->utilities = $utilities;
    }

    /** @return string */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\File\PublicFile';
    }

    /**
     * Serializes a PublicFile entity.
     *
     * @param PublicFile $file
     * @param array      $options
     *
     * @return array
     */
    public function serialize(PublicFile $file, array $options = [])
    {
        return [
            'id' => $file->getId(),
            'size' => $file->getSize(),
            'filename' => $file->getFilename(),
            'directory' => $file->getDirectoryName(),
            'creator' => [],
            'mimeType' => $file->getMimeType(),
            'sourceType' => $file->getSourceType(),
            'url' => $file->getUrl(),
          ];
    }

    /**
     * Deserializes data into a PublicFile into an entity.
     *
     * @param \stdClass       $data
     * @param PublicFile|null $file
     * @param array           $options
     *
     * @return PublicFile
     */
    public function deserialize($data, PublicFile $file = null, array $options = [])
    {
        //this is currently done in FileUtilities
        if (isset($data['id'])) {
            return $this->utilities->getOneBy(['id' => $data['id']]);
        }
    }

    public function getSchema()
    {
        return '#/main/core/publicFile.json';
    }
}
