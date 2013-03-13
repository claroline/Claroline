<?php

namespace Claroline\CoreBundle\Library\Workspace;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    const TYPE_SIMPLE = 'Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace';
    const TYPE_AGGREGATOR = 'Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace';

    private $workspaceType;
    private $workspaceName;
    private $workspaceCode;
    private $isPublic;
    /**
     * If you want to use the role_anonymous from the platform, use
     * 'ROLE_ANONYMOUS'.
     * @var array
     */
    private $roles;
    private $toolsPermissions;
    private $creatorRole;
    private $toolsConfig;

    public function __construct()
    {
        $this->workspaceType = self::TYPE_SIMPLE;
        $ds = DIRECTORY_SEPARATOR;
        $parsedFile = Yaml::parse(__DIR__."{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}workspaces{$ds}default.yml");
        $this->setCreatorRole($parsedFile['creator_role']);
        $this->setRoles($parsedFile['roles']);
        $this->setToolsPermissions($parsedFile['tools_infos']);
        $this->setToolsConfiguration($parsedFile['tools']);
    }

    public static function fromTemplate($templateFile)
    {
        $config = new Configuration();
        $parsedFile = Yaml::parse($templateFile);
        $config->validate($parsedFile);
        $config->setCreatorRole($parsedFile['creator_role']);
        $config->setRoles($parsedFile['roles']);
        $config->setToolsPermissions($parsedFile['tools_infos']);
        $config->setToolsConfiguration($parsedFile['tools']);

        return $config;
    }

    public function setWorkspaceType($type)
    {
        $this->workspaceType = $type;
    }

    public function getWorkspaceType()
    {
        return $this->workspaceType;
    }

    public function setWorkspaceName($name)
    {
        $this->workspaceName = $name;
    }

    public function getWorkspaceName()
    {
        return $this->workspaceName;
    }

    public function setPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    public function isPublic()
    {
        return $this->isPublic;
    }

    public function check()
    {
        if ($this->workspaceType != self::TYPE_SIMPLE && $this->workspaceType != self::TYPE_AGGREGATOR) {
            throw new RuntimeException("Unknown workspace type '{$this->workspaceType}'");
        }

        if (!is_string($this->workspaceName) || 0 === strlen($this->workspaceName)) {
            throw new RuntimeException('Workspace name must be a non empty string');
        }
    }

    public function setWorkspaceCode($workspaceCode)
    {
        $this->workspaceCode = $workspaceCode;
    }

    public function getWorkspaceCode()
    {
        return $this->workspaceCode;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    public function getToolsPermissions()
    {
        return $this->toolsPermissions;
    }

    public function setToolsPermissions(array $toolsPermissions)
    {
        $this->toolsPermissions = $toolsPermissions;
    }

    public function getToolsConfiguration()
    {
        return $this->toolsConfig;
    }

    public function setToolsConfiguration(array $toolsConfig)
    {
        $this->toolsConfig = $toolsConfig;
    }

    public function setCreatorRole($role)
    {
        $this->creatorRole = $role;
    }

    public function getCreatorRole()
    {
        return $this->creatorRole;
    }

    private function validate($parsedFile)
    {
        $errors = array();

        $expectedKeys = array(
            'tools',
            'roles',
            'creator_role',
            'tools_permissions',
            'name'
        );

        foreach ($expectedKeys as $key) {
            if (!isset($parsedFile[$key])) {
                $errors[] = "The entry '{$key}' is missing";
            }
        }

        if (count($errors) === 0) {
            return true;
        } else {
            return $errors;
        }

    }
}