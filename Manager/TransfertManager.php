<?php
/**
 * Created by PhpStorm.
 * User: ezs
 * Date: 13/01/14
 * Time: 15:41
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\ManifestConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\OwnerConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\UsersImporter;
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

    /**
     * Constructor.
     */
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
        $ds = DIRECTORY_SEPARATOR;
        $processor = new Processor();
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $this->setRootPath($path);
        $this->setImporters($path, $data);

        try {
            $usersImporter = $this->getImporterByName('user_importer');
            $groupsConfigurationBuilder = new GroupsConfigurationBuilder();
            $rolesConfigurationBuilder  = new RolesConfigurationBuilder();
            $toolsConfigurationBuilder  = new ToolsConfigurationBuilder();

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

            //roles
            if (isset($data['roles'])) {
                $roles['roles'] = $data['roles'];
                $roles = $processor->processConfiguration($rolesConfigurationBuilder, $roles);
            }
            if (isset($data['rolefiles'])) {
                foreach ($data['rolefiles'] as $rolepath) {
                    $filepath = $path . $ds . $rolepath['path'];
                    $roledata = Yaml::parse(file_get_contents($filepath));
                    $processedConfiguration = $processor->processConfiguration($rolesConfigurationBuilder, $roledata);
                }
            }

            //users
            $users['users'] = array();

            if (isset($data['members']['users'])) {
                $users['users'] = $data['members']['users'];
            }

            if (isset($data['userfiles'])) {
                foreach ($data['userfiles'] as $userpath) {
                    $filepath = $path . $ds . $userpath['path'];
                    $userdata = Yaml::parse(file_get_contents($filepath));
                    foreach ($userdata as $udata) {
                        foreach ($udata as $user) {
                            $users['users'][] = array('user' => $user['user']);
                        }
                    }
                }
            }

            $usersImporter->validate($users);

            //groups
            if (isset($data['members']['groups'])) {
                $groups['groups'] = $data['members']['groups'];
                $users = $processor->processConfiguration($groupsConfigurationBuilder, $groups);

            }
            if (isset($data['groupfiles'])) {
                foreach ($data['groupfiles'] as $grouppath) {
                    $filepath = $path . $ds . $grouppath['path'];
                    $groupdata = Yaml::parse(file_get_contents($filepath));
                    $processedConfiguration = $processor->processConfiguration($groupsConfigurationBuilder, $groupdata);
                }
            }

            //tools
            if (isset($data['tools'])) {
                $tools['tools'] = $data['tools'];
                $processedConfiguration = $processor->processConfiguration($toolsConfigurationBuilder, $tools);
                $this->validateToolsConfig($tools);
            }
            if (isset($data['toolfiles'])) {
                foreach ($data['toolfiles'] as $toolpath) {
                    $filepath = $path . $ds . $toolpath['path'];
                    $toolsdata = Yaml::parse(file_get_contents($filepath));
                    $processedConfiguration = $processor->processConfiguration($toolsConfigurationBuilder, $toolsdata);
                    $this->validateToolsConfig($toolsdata);
                }
            }
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
    private function setImporters($rootPath, $manifest)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setRootPath($rootPath);
            $importer->setManifest($manifest);
        }
    }
}