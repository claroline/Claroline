<?php

namespace Claroline\CoreBundle\Library\Workspace;

use Symfony\Component\Yaml\Yaml;

class TemplateBuilder
{
    private $archive;
    private $config;

    public function __construct()
    {
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
        $this->config['tools'][$name] = array();
    }

    public function removeResourceType($name)
    {
        $key = array_search(array('name' => $name), $this->config['root_perms']['ROLE_WS_MANAGER']['canCreate']);
        unset($this->config['root_perms']['ROLE_WS_MANAGER']['canCreate'][$key]);
    }

    public function removeWidget($name)
    {
        $key = array_search(array('name' => $name, 'is_visible' => true), $this->config['tools']['home']['widget']);
        if (!$key) {
            $key = array_search(array('name' => $name, 'is_visible' => false), $this->config['tools']['home']['widget']);
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
}