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

    public function getListImporters()
    {
        return $this->listImporters;
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

    protected function getImporterByName($name)
    {
        foreach ($this->listImporters as $importer) {
            if ($importer->getName() === $name) {
                return $importer;
            }
        }

        return null;
    }

    /**
     * Platform roles must be on every platforms. They don't need to be created.
     * ROLE_WS_MANAGER is created automatically.
     */
    public static function getDefaultRoles()
    {
        return array(
            'ROLE_USER',
            'ROLE_WS_MANAGER',
            'ROLE_WS_CREATOR',
            'ROLE_ADMIN',
            'ROLE_ANONYMOUS'
        );
    }

    abstract function getName();

    abstract function validate(array $data);
} 