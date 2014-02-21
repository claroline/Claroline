<?php

namespace Claroline\CoreBundle\Library\Transfert;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Importer
{
    private $listImporters;
    private $rootPath;
    private $manifest;

    public function setManifest($path)
    {
        $this->manifest = $path;
    }

    public function getManifest()
    {
        return $this->manifest;
    }

    public function setListImporters(ArrayCollection $importers)
    {
        $this->listImporters = $importers;
    }

    public function setRootPath($rootpath)
    {
        $this->rootPath = $rootpath;
    }

    public function getRootPath()
    {
        return $this->rootPath;
    }

    abstract function getName();

    abstract function validate(array $data);
} 