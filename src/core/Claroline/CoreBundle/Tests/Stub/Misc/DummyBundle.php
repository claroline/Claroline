<?php

namespace Claroline\CoreBundle\Tests\Stub\Misc;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class DummyBundle extends Bundle
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }
}