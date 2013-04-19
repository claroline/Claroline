<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Symfony\Component\Yaml\Yaml;

class TemplateBuilder
{
    private $config;

    public function __construct($config = null)
    {
        $this->config = $config;
    }

    public static function fromTemplate($defaultPath)
    {
        $builder = new TemplateBuilder();
        $archive = new \ZipArchive();
        $archive->open($defaultPath);
        $builder->setArchive($archive);
        $builder->setConfig(Yaml::parse($archive->getFromName('config.yml')));

        return $builder;
    }

    public function addResourceType($name, $role)
    {
        $this->config['root_perms'][$role]['canCreate'][] = array('name' => $name);
    }

    public function addWidget($name)
    {
        $widgetConfiguration = array('name' => $name, 'is_visible' => true);
        $this->config['tools']['home']['widget'][] = $widgetConfiguration;
    }

    public function addTool($name, $displayedName)
    {
        $toolsInfos = array(
            'perms' => array(
                'ROLE_WS_COLLABORATOR',
                'ROLE_WS_MANAGER'
            ),
            'name' => $displayedName
        );

        $this->config['tools_infos'][$name] = $toolsInfos;
        $this->config['tools'][$name] = array('files' => array());
    }

    public function removeResourceType($name)
    {
        $key = array_search(array('name' => $name), $this->config['root_perms']['ROLE_WS_MANAGER']['canCreate']);
        unset($this->config['root_perms']['ROLE_WS_MANAGER']['canCreate'][$key]);
    }

    public function removeWidget($name)
    {
        $key = array_search(
            array('name' => $name, 'is_visible' => true),
            $this->config['tools']['home']['widget']
        );

        if (!$key) {
            $key = array_search(
                array('name' => $name, 'is_visible' => false),
                $this->config['tools']['home']['widget']
            );
        }

        unset($this->config['tools']['home']['widget'][$key]);
    }

    public function removeTool($name)
    {
        unset($this->config['tools_infos'][$name]);
        unset($this->config['tools'][$name]);
    }

    public function write()
    {
        $yaml = Yaml::dump($this->config, 10);
        $this->archive->addFromString('config.yml', $yaml);
        $this->archive->close();
    }

    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setArchive($archive)
    {
        $this->archive = $archive;
    }

    public function getArchive()
    {
        return $this->archive;
    }

    public static function buildDefault($defaultPath)
    {
        $archive = new \ZipArchive();

        if (true === $code = $archive->open($defaultPath, \ZipArchive::CREATE)) {
            $archive->addFromString('config.yml', Yaml::dump(TemplateBuilder::getDefaultConfig(), 10));
            $archive->close();
        } else {
            throw new \Exception(
                "Couldn't open template archive '{$defaultPath}' (error {$code})"
            );
        }
    }

    public static function getDefaultConfig()
    {
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
                    'canCreate' => array()
                ),
                'ROLE_WS_COLLABORATOR' =>
                array(
                    'canEdit' => '0',
                    'canOpen' => '1',
                    'canDelete' => '0',
                    'canCopy' => '0',
                    'canExport' => '1',
                    'canCreate' => array()
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
                        0 => array('name' => 'file'),
                        1 => array('name' => 'directory'),
                        2 => array('name' => 'text'),
                        3 => array('name' => 'resource_shortcut'),
                        4 => array('name' => 'activity')
                    )
                )
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
                    ),
                    'files' => array(),
                ),
                'resource_manager' =>
                array(
                    'root_id' => 28,
                    'resources' => array(),
                    'files' => array(),
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
                array('perms' => array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'), 'name' => 'Ressources'),
                'calendar' =>
                array('perms' => array('ROLE_WS_COLLABORATOR', 'ROLE_WS_MANAGER'), 'name' => 'Calendrier'),
                'parameters' =>
                array('perms' => array('ROLE_WS_MANAGER'), 'name' => 'Paramètres'),
                'group_management' =>
                array('perms' => array('ROLE_WS_MANAGER'), 'name' => 'Groupes'),
                'user_management' =>
                array('perms' => array('ROLE_WS_MANAGER'), 'name' => 'Utilisateurs'),
            ),
            'name' => 'default',
        );
    }
}