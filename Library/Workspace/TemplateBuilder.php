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

use Symfony\Component\Yaml\Yaml;

class TemplateBuilder
{
    private $config;
    private $archive;

    public function __construct(\ZipArchive $archive, $config = null)
    {
        $this->archive = $archive;
        $this->config = $config;
    }

    public static function fromTemplate($defaultPath)
    {
        $archive = self::createArchive($defaultPath);
        $builder = new TemplateBuilder($archive);
        $builder->setConfig(Yaml::parse($archive->getFromName('config.yml')));

        return $builder;
    }

    public function addResourceType($name, $role)
    {
        $this->config['root_perms'][$role]['create'][] = array('name' => $name);

        return $this;
    }

    public function addWidget($name)
    {
        $widgetConfiguration = array('name' => $name, 'is_visible' => true);
        $this->config['tools']['home']['widget'][] = $widgetConfiguration;

        return $this;
    }

    /**
     * @param $filePath
     * @param $hashName
     * @param $fileName
     * @param $parentId
     * @param $fileId
     *
     * @return $this
     */
    public function addFile($filePath, $hashName, $fileName, $parentId, $fileId)
    {
        $fileArray = array(
            'type' => 'file',
            'perms' => $this->getDefaultResourcePerms(),
            'parent' => $parentId,
            'id' => $fileId,
            'name' => $fileName,
            'files' => array($hashName)
        );

        $this->config['tools']['resource_manager']['resources'][] = $fileArray;
        $this->config['tools']['resource_manager']['files'][] = $hashName;

        $this->archive->addFile($filePath, $hashName);

        return $this;
    }

    public function addDirectory($name, $directoryId)
    {
        $directoryArray = array(
            'type' => 'directory',
            'name' => $name,
            'id' => $directoryId,
            'children' => array(),
            'perms' => $this->getDefaultResourcePerms()
        );

        $this->config['tools']['resource_manager']['directory'][] = $directoryArray;

        return $this;
    }

    public function addTool($name, $displayedName)
    {
        $toolsInfos = array(
            'perms' => array('ROLE_WS_COLLABORATOR'),
            'name' => $displayedName
        );

        $this->config['tools_infos'][$name] = $toolsInfos;
        $this->config['tools'][$name] = array('files' => array());

        return $this;
    }

    public function removeResourceType($name)
    {
        $key = array_search(array('name' => $name), $this->config['root_perms']['ROLE_WS_MANAGER']['create']);
        unset($this->config['root_perms']['ROLE_WS_MANAGER']['create'][$key]);

        return $this;
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

        return $this;
    }

    public function removeTool($name)
    {
        unset($this->config['tools_infos'][$name]);
        unset($this->config['tools'][$name]);

        return $this;
    }

    public function write()
    {
        $yaml = Yaml::dump($this->config, 10);
        $this->archive->addFromString('config.yml', $yaml);
        $this->archive->close();

        return $this;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public static function buildDefault($defaultPath)
    {
        $archive = self::createArchive($defaultPath);
        $archive->addFromString('config.yml', Yaml::dump(TemplateBuilder::getDefaultConfig(), 10));
        $archive->close();
    }

    public static function getDefaultConfig()
    {
        return array(
            'root_perms' => array(
                'ROLE_WS_COLLABORATOR' => array(
                    'edit' => '0',
                    'open' => '1',
                    'delete' => '0',
                    'copy' => '0',
                    'export' => '1',
                    'create' => array()
                )
            ),
            'tools' => array(
                'home' => array(
                    'widget' => array(
                        0 => array(
                            'name' => 'core_resource_logger',
                            'is_visible' => true,
                        )
                    ),
                    'files' => array()
                ),
                'resource_manager' => array(
                    'root_id' => 1,
                    'resources' => array(),
                    'files' => array()
                )
            ),
            'roles' => array(
                'ROLE_WS_COLLABORATOR' => 'collaborator'
            ),
            'tools_infos' => array(
                'home' => array(
                    'perms' => array(
                        1 => 'ROLE_WS_COLLABORATOR'
                    ),
                    'name' => 'home'
                ),
                'resource_manager' => array(
                    'perms' => array('ROLE_WS_COLLABORATOR'),
                    'name' => 'resource_manager'
                ),
                'agenda' => array(
                    'perms' => array('ROLE_WS_COLLABORATOR'),
                    'name' => 'agenda'
                ),
                'parameters' => array(
                    'perms' => array(),
                    'name' => 'parameters'
                ),
                'users' => array(
                    'perms' => array(),
                    'name' => 'users'
                ),
                'logs' => array(
                    'perms' => array(),
                    'name' => 'logs'
                ),
            ),
            'name' => 'default'
        );
    }

    public function getDefaultResourcePerms()
    {
        return array(
            'ROLE_WS_COLLABORATOR' => array(
                'edit' => '0',
                'open' => '1',
                'delete' => '0',
                'copy' => '0',
                'export' => '1',
                'create' => array()
            ),
            'ROLE_WS_MANAGER' => array(
                'edit' => '1',
                'open' => '1',
                'delete' => '1',
                'copy' => '1',
                'export' => '1',
                'create' => array()
            )
        );
    }

    private function findDirectory(array $directoriesArray, $searchId)
    {
        foreach ($directoriesArray as $directory) {
            if ($searchId == $directory['id']) {
                return $directory;
            }
            $this->findDirectory($directory['children'], $searchId);
        }
    }

    private static function createArchive($path)
    {
        $archive = new \ZipArchive();

        if (true !== $code = $archive->open($path, \ZipArchive::CREATE)) {
            throw new \Exception(
                "Couldn't open template archive '{$path}' (error {$code})"
            );
        }

        return $archive;
    }
}
