<?php

namespace Claroline\ScormBundle\Library\Export\Manifest;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class Scorm12Manifest extends AbstractScormManifest
{
    public function __construct(ResourceNode $node, array $resources)
    {
        throw new \Exception('Export in SCORM 1.2 version is not yet implemented.');
    }

    protected function getSchemaVersion()
    {
        return '1.2';
    }
}
