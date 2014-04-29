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
use Claroline\CoreBundle\Library\Transfert\ManifestConfiguration;
use Doctrine\Common\Collections\ArrayCollection;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\GroupsConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\RolesConfigurationBuilder;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\ToolsConfigurationBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Entity\Resource\Directory;

/**
 * @DI\Service("claroline.manager.transfert_manager")
 */
class TransfertManager
{
    private $listImporters;
    private $rootPath;
    private $om;
    private $container;

    /**
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" = @DI\Inject("service_container")
     * })
     */
    public function __construct($om, $container)
    {
        $this->listImporters = new ArrayCollection();
        $this->om = $om;
        $this->container = $container;
    }

    public function addImporter(Importer $importer)
    {
        return $this->listImporters->add($importer);
    }

    /**
     * Import a workspace
     */
    public function validate(array $data)
    {
        $usersImporter  = $this->getImporterByName('user');
        $groupsImporter = $this->getImporterByName('groups');
        $rolesImporter  = $this->getImporterByName('roles');
        $toolsImporter  = $this->getImporterByName('tools');
        $ownerImporter  = $this->getImporterByName('owner');
        $importer = $this->getImporterByName('workspace_properties');

        //owner
        if (isset($data['members']['owner'])) {
            $owner['owner'] = $data['members']['owner'];
            $ownerImporter->validate($owner);
        }

        //properties
        if (isset($data['properties'])) {
            $properties['properties'] = $data['properties'];
            $importer->validate($properties);
        }

        if (isset($data['roles'])) {
            $roles['roles'] = $data['roles'];
            $rolesImporter->validate($roles);
        }

        if (isset($data['members']['users'])) {
            $users['users'] = $data['members']['users'];
            $usersImporter->validate($users);
        }

        if (isset($data['members']['groups'])) {
            $groups['users'] = $data['members']['groups'];
            $groupsImporter->validate($groups);
        }

        if (isset ($data['tools'])) {
            $tools['tools'] = $data['tools'];
//            var_dump($tools);
            $toolsImporter->validate($tools);
        }

    }

    public function import(array $data)
    {
        $ownerImporter = $this->getImporterByName('owner');
        $ownerImporter->import($data['members']['owner'], null);
    }

    public function createWorkspace($configuration, $owner)
    {
        $this->om->startFlushSuite();
        $data = $configuration->getData();
        $this->setImporters('', $data);
        $this->validate($data);
        $workspace = new SimpleWorkspace();
        $workspace->setName($configuration->getWorkspaceName());
        $workspace->setCode($configuration->getWorkspaceCode());
        $workspace->setDescription($configuration->getWorkspaceDescription());
        $workspace->setGuid($this->container->get('claroline.utilities.misc')->generateGuid());
        $workspace->setDisplayable($configuration->isDisplayable());
        $workspace->setSelfRegistration($configuration->getSelfRegistration());
        $workspace->setSelfUnregistration($configuration->getSelfUnregistration());
        $date = new \Datetime(date('d-m-Y H:i'));
        $workspace->setCreationDate($date->getTimestamp());

//        if ($owner) {
//            $workspace->setCreator($owner);
//        }

        $this->om->persist($workspace);
        $this->om->flush();

        //load roles
        $entityRoles = $this->getImporterByName('roles')->import($data['roles'], $workspace);
        //The manager role is required for every workspace
        $entityRoles['ROLE_WS_MANAGER'] = $this->container->get('claroline.manager.role_manager')->createWorkspaceRole(
            "ROLE_WS_MANAGER_{$workspace->getGuid()}",
            'manager',
            $workspace,
            true
        );

        $owner->addRole($entityRoles['ROLE_WS_MANAGER']);
        $this->om->persist($owner);

        $tools = $this->getImporterByName('tools')->import($data['tools'], $workspace, $entityRoles);

        $dir = new Directory();
        $dir->setName($workspace->getName());

        //check if the resource manager configuration exists
        $root = $this->container->get('claroline.manager.resource_manager')->create(
            $dir,
            $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByName('directory'),
            $owner,
            $workspace,
            null,
            null,
            array()
        );

        $this->om->endFlushSuite();

        return $workspace;
    }

    private function setRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
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
     * @param $configuration
     */
    private function setImporters($rootPath, $configuration)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setRootPath($rootPath);
            $importer->setConfiguration($configuration);
            $importer->setListImporters($this->listImporters);
        }
    }

    public function dumpConfiguration()
    {
        $dumper = new YamlReferenceDumper($this->importer);

        $string = '';
        $string .= $dumper->dump($this->getImporterByName('workspace_properties'));
        $string .= $dumper->dump($this->getImporterByName('owner'));
        $string .= $dumper->dump($this->getImporterByName('user'));
        $string .= $dumper->dump($this->getImporterByName('groups'));
        $string .= $dumper->dump($this->getImporterByName('roles'));
        $string .= $dumper->dump($this->getImporterByName('tools'));

        return $string;
    }
}