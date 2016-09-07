<?php

namespace Claroline\ScormBundle\Library\Export\Manifest;

class Scorm2004Manifest extends AbstractScormManifest
{
    protected function getSchemaVersion()
    {
        return '2004 3rd Edition';
    }
}
