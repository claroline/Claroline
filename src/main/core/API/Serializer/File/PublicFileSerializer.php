<?php

namespace Claroline\CoreBundle\API\Serializer\File;

use Claroline\CoreBundle\Entity\File\PublicFile;

/**
 * @todo move me in AppBundle
 */
class PublicFileSerializer
{
    private $utilities;

    public function __construct($utilities)
    {
        $this->utilities = $utilities;
    }

    /** @return string */
    public function getClass()
    {
        return PublicFile::class;
    }

    public function getName()
    {
        return 'public_file';
    }

    /**
     * Serializes a PublicFile entity.
     *
     * @return array
     */
    public function serialize(PublicFile $file, array $options = [])
    {
        return [
            'id' => $file->getId(),
            'type' => $file->getMimeType(),
            'name' => $file->getFilename(),
            'size' => $file->getSize(),
            'directory' => $file->getDirectoryName(), // I'm not sure this is needed
            'sourceType' => $file->getSourceType(),
            'url' => $file->getUrl(),

            // deprecated use `type` / `name` (this is to be compliant with the js File API)
            'mimeType' => $file->getMimeType(),
            'filename' => $file->getFilename(),
          ];
    }

    /**
     * Deserializes data into a PublicFile into an entity.
     *
     * @param \stdClass $data
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
