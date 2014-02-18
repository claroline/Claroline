<?php
/**
 * Created by PhpStorm.
 * User: ezs
 * Date: 13/01/14
 * Time: 15:41
 */

namespace Claroline\CoreBundle\Manager;


use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ImporterInterface;
use Claroline\CoreBundle\Library\Transfert\ManifestConfiguration;
use Claroline\CoreBundle\Library\Transfert\WorkspacePropertiesImporter;
use Claroline\CoreBundle\Library\Transfert\UsersImporter;
use Claroline\CoreBundle\Library\Transfert\GroupsImporter;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\PropertiesConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\OwnerConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\UsersConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\RolesConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\ToolsConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\HomeConfigurationBuilder;

class TransfertManager
{
    private $listImporters;
    private $workspaceImporter;
    private $userImporter;
    private $groupImporter;
    private $rootPath;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *  "workspaceImporter" = @DI\Inject("claroline.importer.workspace_properties"),
     *  "userImporter"      = @DI\Inject("claroline.importer.user"),
     *  "groupsImporter"    = @DI\Inject("claroline.importer.group"),
     * })
     */
    public function __construct(
        WorkspacePropertiesImporter $workspaceImporter,
        UsersImporter $userImporter,
        GroupsImporter $groupsImporter
    )
    {
        $this->userImporter = $userImporter;
        $this->groupsImporter = $groupsImporter;
        $this->workspaceImporter = $workspaceImporter;
        $this->listImporters = new ArrayCollection();
    }

    public function addImporter(ImporterInterface $importer)
    {
        return $this->listImporters->add($importer);
    }

    public function importWorkspace($path)
    {
        $this->setRootPath($path);
        $ds = DIRECTORY_SEPARATOR;
        $processor = new Processor();
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));

        try {
            $usersConfigurationBuilder = new UsersConfigurationBuilder();
            $groupsConfigurationBuilder = new GroupsConfigurationBuilder();
            $rolesConfigurationBuilder = new RolesConfigurationBuilder();
            $toolsConfigurationBuilder = new ToolsConfigurationBuilder();

            //owner
            if (isset($data['members']['owner'])) {
                $owner['owner'] = $data['members']['owner'];
                $ownerConfigurationBuilder = new OwnerConfigurationBuilder();
                $owner = $processor->processConfiguration($ownerConfigurationBuilder, $owner);
            }
            //properties
            $propertiesConfigurationBuilder = new PropertiesConfigurationBuilder();
            $properties['properties'] = $data['properties'];
            $properties = $processor->processConfiguration($propertiesConfigurationBuilder, $properties);

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
            if (isset($data['members']['users'])) {
                $users['users'] = $data['members']['users'];
                $users = $processor->processConfiguration($usersConfigurationBuilder, $users);
            }
            if (isset($data['userfiles'])) {
                foreach ($data['userfiles'] as $userpath) {
                    $filepath = $path . $ds . $userpath['path'];;
                    $userdata = Yaml::parse(file_get_contents($filepath));
                    $processedConfiguration = $processor->processConfiguration($usersConfigurationBuilder, $userdata);
                }
            }

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
                $tools = $processor->processConfiguration($toolsConfigurationBuilder, $tools);

            }
            if (isset($data['toolfiles'])) {
                foreach ($data['toolfiles'] as $toolpath) {
                    $filepath = $path . $ds . $toolpath['path'];
                    $tooldata = Yaml::parse(file_get_contents($filepath));
                    $processedConfiguration = $processor->processConfiguration($toolsConfigurationBuilder, $tooldata);
                    $this->validateTools($tooldata);

                }
            }

            //home
            $builder = new HomeConfigurationBuilder();
            $homepath = $path . $ds . 'tools' . $ds . 'home.yml';
            $homedata = Yaml::parse(file_get_contents($homepath));
            $processor->processConfiguration($builder, $homedata);

        } catch (\Exception $e) {
            var_dump(array($e->getMessage())) ;
        }
    }


    private function validateTools($tooldata)
    {

    }

    private function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    private function getRootPath()
    {
        return $this->rootPath;
    }
}