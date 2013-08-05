<?php

namespace Claroline\CoreBundle\Library\Configuration;

class UnwritableException extends \Exception
{
    private $path;

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}
