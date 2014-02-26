<?php

namespace Claroline\CoreBundle\Library\Transfert;

use Doctrine\Common\Collections\ArrayCollection;

abstract class Importer
{
    private $listImporters;
    private $rootPath;
    private $configuration;

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

    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    abstract function getName();

    abstract function validate(array $data);
} 