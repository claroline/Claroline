<?php

namespace Claroline\CoreBundle\Library\Transfert;

use Doctrine\Common\Collections\ArrayCollection;

abstract class ToolImporter
{
    private $roles;
    private $rootPath;

    public function setRole(array $roles)
    {
        $this->roles = $roles;
    }

    public function getRoles()
    {
        return $this->roles;
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
} 