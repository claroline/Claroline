<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Transfert;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Doctrine\Common\Collections\ArrayCollection;

abstract class Importer
{
    use LoggableTrait;

    private $listImporters;
    private $rootPath;
    private $configuration;
    private $owner;
    private $workspace;
    private static $isStrict;
    private $roles = [];
    private $_data;
    private $_files;

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

        return;
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
        self::$isStrict = $boolean;
    }

    public static function isStrict()
    {
        return self::$isStrict;
    }

    public function setWorkspace(Workspace $workspace)
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
        return [
            'ROLE_USER',
            'ROLE_WS_MANAGER',
            'ROLE_WS_CREATOR',
            'ROLE_ADMIN',
            'ROLE_ANONYMOUS',
        ];
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

    public function getPriority()
    {
        return 0;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function setExtendedData(&$_data)
    {
        $this->_data = &$_data;
    }

    //this is only supposed to keep backward compatibility... damn references !
    //I wish I could pass _data to the export function but it would break pretty much every plugin
    public function &getExtendedData()
    {
        return $this->_data;
    }

    abstract public function getName();

    abstract public function validate(array $data);

     /**
      * @param Workspace $workspace
      * @param array $files
      * @param mixed $object
      * @param mixed $data
      */
     abstract public function export($workspace, array &$files, $object);
}
