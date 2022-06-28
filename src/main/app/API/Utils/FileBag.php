<?php

namespace Claroline\AppBundle\API\Utils;

class FileBag
{
    private $files;

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
        // be sure to use unix directory separator
        $key = str_replace('\\', '/', $key);

        if (!empty($this->files[$key])) {
            return $this->files[$key];
        }

        return null;
    }
}
