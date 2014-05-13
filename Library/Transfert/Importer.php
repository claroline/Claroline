<?php

namespace Claroline\CoreBundle\Library\Transfert;

use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Entity\User;

abstract class Importer
{
    private $listImporters;
    private $rootPath;
    private $configuration;
    private $owner;
    private $isStrict;
    private $roles = array();

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

    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setStrict($boolean)
    {
        $this->isStrict = $boolean;
    }

    public function isStrict()
    {
        return $this->isStrict;
    }

    public function setWorkspace($workspace)
    {
        $this->workspace = $workspace;
    }

    public function getWorkspace()
    {
        return $this->workspace;
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

    public function setRolesEntity(array $roles)
    {
        $this->roles = $roles;
    }

    public function getRolesEntity()
    {
        return $this->roles;
    }

    public function addRoleEntity($role)
    {
        $this->roles[] = $role;
    }

    abstract function getName();

    abstract function validate(array $data);
} 