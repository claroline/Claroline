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

    //@todo refactoring __construct/fromTemplate because the ziparchive is opened
    //twice with fromtemplate.
    public function __construct()
    {
        $this->workspaceType = self::TYPE_SIMPLE;
        $ds = DIRECTORY_SEPARATOR;
        $this->templateFile = __DIR__."{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}templates{$ds}default.zip";
        $archive = new \ZipArchive();
        $archive->open($this->templateFile);
        //$parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        //Use claroline:template:dump_default command and copy/paste the result in getDefaults
        //if the default template changed.
        //Speeds up the workspace creation by 0.1 second / workspace.
        $parsedFile = $this->getDefault();
        $this->setCreatorRole($parsedFile['creator_role']);
        $this->setRoles($parsedFile['roles']);
        $this->setToolsPermissions($parsedFile['tools_infos']);
        $this->setToolsConfiguration($parsedFile['tools']);
        $this->setPermsRootConfiguration($parsedFile['root_perms']);
        $this->setArchive($this->templateFile);
    }

    public static function fromTemplate($templateFile)
    {
        $archive = new \ZipArchive();
        $archive->open($templateFile);
        $config = new Configuration();
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $archive->close();
        $config->validate($parsedFile);
        $config->setCreatorRole($parsedFile['creator_role']);
        $config->setRoles($parsedFile['roles']);
        $config->setToolsPermissions($parsedFile['tools_infos']);
        $config->setToolsConfiguration($parsedFile['tools']);
        $config->setPermsRootConfiguration($parsedFile['root_perms']);
        $config->setArchive($templateFile);

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

    private function getDefault() {
        return array(
            'root_perms' =>
            array(
                'ROLE_WS_VISITOR' =>
                array(
                    'canEdit' => '0',
                    'canOpen' => '0',
                    'canDelete' => '0',
                    'canCopy' => '0',
                    'canExport' => '0',
                    'canCreate' =>
                    array(
                    ),
                ),
                'ROLE_WS_COLLABORATOR' =>
                array(
                    'canEdit' => '0',
                    'canOpen' => '1',
                    'canDelete' => '0',
                    'canCopy' => '0',
                    'canExport' => '1',
                    'canCreate' =>
                    array(
                    ),
                ),
                'ROLE_WS_MANAGER' =>
                array(
                    'canEdit' => '1',
                    'canOpen' => '1',
                    'canDelete' => '1',
                    'canCopy' => '1',
                    'canExport' => '1',
                    'canCreate' =>
                    array(
                        0 =>
                        array(
                            'name' => 'file',
                        ),
                        1 =>
                        array(
                            'name' => 'directory',
                        ),
                        2 =>
                        array(
                            'name' => 'text',
                        ),
                        3 =>
                        array(
                            'name' => 'resource_shortcut',
                        ),
                        4 =>
                        array(
                            'name' => 'activity',
                        ),
                        5 =>
                        array(
                            'name' => 'claroline_site',
                        ),
                        6 =>
                        array(
                            'name' => 'claroline_forum',
                        ),
                        7 =>
                        array(
                            'name' => 'claroline_example',
                        ),
                        8 =>
                        array(
                            'name' => 'icap_referencebank',
                        ),
                        9 =>
                        array(
                            'name' => 'ujm_exercise',
                        ),
                    ),
                ),
            ),
            'tools' =>
            array(
                'home' =>
                array(
                    'widget' =>
                    array(
                        0 =>
                        array(
                            'name' => 'core_resource_logger',
                            'is_visible' => true,
                        ),
                        1 =>
                        array(
                            'name' => 'claroline_rssreader',
                            'is_visible' => true,
                            'config' =>
                            array(
                                'url' => NULL,
                            ),
                        ),
                        2 =>
                        array(
                            'name' => 'claroline_mywidget1',
                            'is_visible' => true,
                        ),
                    ),
                    'files' =>
                    array(
                    ),
                ),
                'resource_manager' =>
                array(
                    'root_id' => 28,
                    'resources' =>
                    array(
                    ),
                    'files' =>
                    array(
                    ),
                ),
            ),
            'roles' =>
            array(
                'ROLE_WS_VISITOR' => 'visitor',
                'ROLE_WS_COLLABORATOR' => 'collaborator',
                'ROLE_WS_MANAGER' => 'manager',
            ),
            'creator_role' => 'ROLE_WS_MANAGER',
            'tools_infos' =>
            array(
                'home' =>
                array(
                    'perms' =>
                    array(
                        0 => 'ROLE_WS_VISITOR',
                        1 => 'ROLE_WS_COLLABORATOR',
                        2 => 'ROLE_WS_MANAGER',
                    ),
                    'name' => 'Accueil',
                ),
                'resource_manager' =>
                array(
                    'perms' =>
                    array(
                        0 => 'ROLE_WS_COLLABORATOR',
                        1 => 'ROLE_WS_MANAGER',
                    ),
                    'name' => 'Ressources',
                ),
                'calendar' =>
                array(
                    'perms' =>
                    array(
                        0 => 'ROLE_WS_COLLABORATOR',
                        1 => 'ROLE_WS_MANAGER',
                    ),
                    'name' => 'Calendrier',
                ),
                'parameters' =>
                array(
                    'perms' =>
                    array(
                        0 => 'ROLE_WS_MANAGER',
                    ),
                    'name' => 'Paramètres',
                ),
                'group_management' =>
                array(
                    'perms' =>
                    array(
                        0 => 'ROLE_WS_MANAGER',
                    ),
                    'name' => 'Groupes',
                ),
                'user_management' =>
                array(
                    'perms' =>
                    array(
                        0 => 'ROLE_WS_MANAGER',
                    ),
                    'name' => 'Utilisateurs',
                ),
            ),
            'name' => 'default',
        );
    }
}