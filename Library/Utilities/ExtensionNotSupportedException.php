<?php

namespace Claroline\CoreBundle\Library\Utilities;

class ExtensionNotSupportedException extends \Exception
{
    private $extension;

    public function setExtension($ext)
    {
        $this->extension = $ext;
    }

    public function getExtension()
    {
        return $this->extension;
    }
}
