<?php

namespace Claroline\AppBundle\API\Utils;

class FileBag
{
    public function __construct()
    {
        $this->files = [];
    }

    public function add($newPath, $location)
    {
        $this->files[$newPath] = $location;
    }

    public function all()
    {
        return $this->files;
    }

    public function get($key)
    {
        return $this->files[$key];
    }
}
