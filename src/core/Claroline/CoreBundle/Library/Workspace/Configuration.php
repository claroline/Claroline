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
    private $tools;
    private $toolsPermissions;
    private $rootPermissions;
    private $creatorRole;

    public function __construct()
    {
        $this->workspaceType = self::TYPE_SIMPLE;
        $this->isPublic = true;
        $this->roles = array(
            'ROLE_WS_VISITOR' => 'visitor',
            'ROLE_WS_COLLABORATOR' => 'collaborator',
            'ROLE_WS_MANAGER' => 'manager'
        );
        $this->tools = array(
            'home',
            'resource_manager',
            'calendar',
            'parameters',
            'group_management',
            'user_management'
        );
        $this->toolsPermissions = array(
            'home' => array(
                'ROLE_WS_VISITOR',
                'ROLE_WS_COLLABORATOR',
                'ROLE_WS_MANAGER'
            ),
            'resource_manager' => array(
                'ROLE_WS_COLLABORATOR',
                'ROLE_WS_MANAGER'
            ),
            'calendar' => array(
                'ROLE_WS_COLLABORATOR',
                'ROLE_WS_MANAGER'
            ),
            'parameters' => array('ROLE_WS_MANAGER'),
            'group_management' => array('ROLE_WS_MANAGER'),
            'user_management' => array('ROLE_WS_MANAGER')
        );
        $this->rootPermissions = array(
            'ROLE_WS_VISITOR' => array(
                'canCopy' => false,
                'canOpen' => false,
                'canEdit' => false,
                'canDelete' => false,
                'canExport' => false,
                'canCreate' => false
             ),
            'ROLE_WS_COLLABORATOR' => array(
                'canCopy' => false,
                'canOpen' => true,
                'canEdit' => false,
                'canDelete' => false,
                'canExport' => true,
                'canCreate' => false
            ),
            'ROLE_WS_MANAGER' => array(
                'canCopy' => true,
                'canOpen' => true,
                'canEdit' => true,
                'canDelete' => true,
                'canExport' => true,
                'canCreate' => true
            )
        );
        $this->creatorRole = 'ROLE_WS_MANAGER';
    }

    public static function fromTemplate($templateFile)
    {
        $config = new Configuration();
        $parsedFile = Yaml::parse($templateFile);
        $config->validate($parsedFile);
        $config->setCreatorRole($parsedFile['creator_role']);
        $config->setRoles($parsedFile['roles']);
        $config->setTools(array_keys($parsedFile['tools_permissions']));
        $config->setToolsPermissions($parsedFile['tools_permissions']);
        $config->setRootPermissions($parsedFile['resources_permissions']);

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

    public function getTools()
    {
        return $this->tools;
    }

    public function setTools(array $tools)
    {
        $this->tools = $tools;
    }

    public function getToolsPermissions()
    {
        return $this->toolsPermissions;
    }

    public function setToolsPermissions(array $toolsPermissions)
    {
        $this->toolsPermissions = $toolsPermissions;
    }

    public function getRootPermissions()
    {
        return $this->rootPermissions;
    }

    public function setRootPermissions(array $rootPermissions)
    {
        $this->rootPermissions = $rootPermissions;
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
        return true;
    }
}