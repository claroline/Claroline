<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Claroline\CoreBundle\Library\Transfert\ManifestConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\OwnerConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\RolesConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\ToolsConfigurationBuilder;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.transfert_manager")
 */
class TransfertManager
{
    private $listImporters;
    private $rootPath;
    private $resolver;


    public function __construct()
    {
        $this->listImporters = new ArrayCollection();
    }

    public function addImporter(Importer $importer)
    {
        return $this->listImporters->add($importer);
    }

    /**
     * Import a workspace
     *
     * @param $path
     */
    public function validate($path)
    {
        $resolver = new Resolver($path);

        $ds = DIRECTORY_SEPARATOR;
        $processor = new Processor();
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $data = $resolver->resolve();
        $this->setRootPath($path);
        $this->setImporters($path, $data);
        $usersImporter  = $this->getImporterByName('user_importer');
        $groupsImporter = $this->getImporterByName('groups_importer');
        $rolesImporter  = $this->getImporterByName('roles_importer');
        $toolsImporter  = $this->getImporterByName('tools_importer');

        try {
            //owner
            if (isset($data['members']['owner'])) {
                $owner['owner'] = $data['members']['owner'];
                $ownerConfigurationBuilder = new OwnerConfigurationBuilder();
                $owner = $processor->processConfiguration($ownerConfigurationBuilder, $owner);
            }

            //properties
            $properties['properties'] = $data['properties'];
            $importer = $this->getImporterByName('workspace_properties');
            $importer->validate($properties);

            $roles['roles'] = $data['roles'];
            $users['users'] = $data['members']['users'];
            $groups['users'] = $data['members']['groups'];
            $tools['tools'] = $data['tools'];
            $rolesImporter->validate($roles);
            $usersImporter->validate($users);
            $groupsImporter->validate($groups);
            $toolsImporter->validate($tools);
            //$this->validateToolsConfig($tools);

        } catch (\Exception $e) {
            var_dump(get_class($e));
            var_dump(array($e->getMessage())) ;
        }
    }

    public function import($path)
    {
        $this->validate($path);
        //do other things
    }

    private function validateToolsConfig(array $tooldata)
    {
        foreach ($tooldata['tools'] as $tool) {
            $toolImporter = null;

            foreach ($this->listImporters as $importer) {
                if ($importer->getName() == $tool['tool']['name']) {
                    $toolImporter = $importer;
                    $toolImporter->setListImporters($this->listImporters);
                }
            }

            if (isset ($tool['tool']['config']) && $toolImporter) {
                $ds = DIRECTORY_SEPARATOR;
                $filepath = $this->getRootPath() . $ds . $tool['tool']['config'];
                //@todo error handling if path doesn't exists
                $tooldata =  Yaml::parse(file_get_contents($filepath));
                $toolImporter->validate($tooldata);
            }

            if (isset($tool['tool']['data']) && $toolImporter) {
                $tooldata = $tool['tool']['data'];
                $toolImporter->validate($tooldata);
            }
        }
    }

    private function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    private function getRootPath()
    {
        return $this->rootPath;
    }

    private function getImporterByName($name)
    {
        foreach ($this->listImporters as $importer) {
            if ($importer->getName() === $name) {
                return $importer;
            }
        }

        return null;
    }

    /**
     * Inject the rootPath
     *
     * @param $rootPath
     * @param $manifest
     */
    private function setImporters($rootPath, $configuration)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setRootPath($rootPath);
            $importer->setConfiguration($configuration);
        }
    }
}