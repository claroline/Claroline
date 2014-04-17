<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Workspace;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Workspace\Exception\BaseRoleException;

class Configuration
{
    const TYPE_SIMPLE = 'Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace';
    const TYPE_AGGREGATOR = 'Claroline\CoreBundle\Entity\Workspace\AggregatorWorkspace';

    private $workspaceType;
    private $workspaceName;
    private $workspaceCode;
    private $workspaceDescription;
    private $displayable = false;
    private $selfRegistration = false;
    private $selfUnregistration = false;
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

    public function __construct($template, $full = true)
    {
        if ($full) {
            $this->templateFile = $template;
            $this->workspaceType = self::TYPE_SIMPLE;
            $archive = new \ZipArchive();

            if (true === $code = $archive->open($template)) {
                $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
                $archive->close();
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
    
    
    public function setWorkspaceDescription($workspaceDescription)
    {
        $this->workspaceDescription = $workspaceDescription;
    }

    public function getWorkspaceDescription()
    {
        return $this->workspaceDescription;
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

    public function setDisplayable($displayable)
    {
        $this->displayable = $displayable;
    }

    public function isDisplayable()
    {
        return $this->displayable;
    }

    public function setSelfRegistration($selfRegistration)
    {
        $this->selfRegistration = $selfRegistration;
    }

    public function getSelfRegistration()
    {
        return $this->selfRegistration;
    }

    public function setSelfUnregistration($selfUnregistration)
    {
        $this->selfUnregistration = $selfUnregistration;
    }

    public function getSelfUnregistration()
    {
        return $this->selfUnregistration;
    }
}
