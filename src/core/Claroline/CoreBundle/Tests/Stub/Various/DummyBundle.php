<?php

namespace Claroline\CoreBundle\Tests\Stub\Various;

class DummyBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    private $path;
    private $installationIndex;
    
    public function __construct($path, $installationIndex) 
    {
        $this->path = $path;
        $this->installationIndex = $installationIndex;
    }
    
    public function getPath()
    {
        return $this->path;
    }
}