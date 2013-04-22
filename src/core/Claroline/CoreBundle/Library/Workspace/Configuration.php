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
    private $permsRootConfig;
    private $templateFile;

    public function __construct($template)
    {
        $this->templateFile = $template;
        $this->workspaceType = self::TYPE_SIMPLE;
        $archive = new \ZipArchive();

        if (true === $code = $archive->open($template)) {
            $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
            $archive->close();
            $this->setCreatorRole($parsedFile['creator_role']);
            $this->setRoles($parsedFile['roles']);
            $this->setToolsPermissions($parsedFile['tools_infos']);
            $this->setToolsConfiguration($parsedFile['tools']);
            $this->setPermsRootConfiguration($parsedFile['root_perms']);
        } else {
            throw new \Exception(
                "Couldn't open template archive '{$template}' (error {$code})"
            );
        }
    }

    /**
     * @todo this method is useless (constructor should be enough now)
     */
    public static function fromTemplate($templateFile)
    {
        return new self($templateFile);
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

    public function setArchive($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    public function getArchive()
    {
        return $this->templateFile;
    }

    public function setPermsRootConfiguration($config)
    {
        $this->permsRootConfig = $config;
    }

    public function getPermsRootConfiguration()
    {
        return $this->permsRootConfig;
    }
}