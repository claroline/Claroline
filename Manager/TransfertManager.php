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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Library\Utilities\FileSystem;
use Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Psr\Log\LoggerInterface;

/**
 * @DI\Service("claroline.manager.transfert_manager")
 */
class TransfertManager
{
    use LoggableTrait;

    private $listImporters;
    private $rootPath;
    private $om;
    private $container;
    private $data;
    private $workspace;

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
        $this->data = array();
        $this->workspace = null;
    }

    public function addImporter(Importer $importer)
    {
        return $this->listImporters->add($importer);
    }

    /**
     * Import a workspace
     */
    public function validate(array $data, $validateProperties = true)
    {
        $groupsImporter = $this->getImporterByName('groups');
        $rolesImporter  = $this->getImporterByName('roles');
        $toolsImporter  = $this->getImporterByName('tools');
        $importer       = $this->getImporterByName('workspace_properties');
        $usersImporter  = $this->getImporterByName('user');

        //properties
        if ($validateProperties) {
            if (isset($data['properties'])) {
                $properties['properties'] = $data['properties'];
                $importer->validate($properties);
            }
        }

        if (isset($data['roles'])) {
            $roles['roles'] = $data['roles'];
            $rolesImporter->validate($roles);
        }

        if (isset ($data['tools'])) {
            $tools['tools'] = $data['tools'];
            $toolsImporter->validate($tools);
        }

    }

    public function import(Configuration $configuration)
    {
        $owner = $this->container->get('security.token_storage')->getToken()->getUser();
        $configuration->setOwner($owner);
        $this->setImporters($configuration, $data);
        $this->validate($data);

        //initialize the configuration
        $configuration->setWorkspaceName($data['properties']['name']);
        $configuration->setWorkspaceCode($data['properties']['code']);
        $configuration->setDisplayable($data['properties']['visible']);
        $configuration->setSelfRegistration($data['properties']['self_registration']);
        $configuration->setSelfUnregistration($data['properties']['self_unregistration']);

        $this->createWorkspace($configuration, $owner, true);
    }

    /**
     * Populates a workspace content with the content of an zip archive. In other words, it ignores the
     * many properties of the configuration object and use an existing workspace as base.
     *
     * This will set the $this->data var
     * This will set the $this->workspace var
     *
     * @param Workspace $workspace
     * @param Confuguration $configuration
     * @param Directory $root
     * @param array $entityRoles
     * @param bool $isValidated
     * @param bool $importRoles
     */
    public function populateWorkspace(
        Workspace $workspace,
        Configuration $configuration,
        Directory $root,
        array $entityRoles,
        $isValidated = false,
        $importRoles = true
    )
    {
        $this->om->startFlushSuite();
        $data = $configuration->getData();
        $data = $this->reorderData($data);
        //now we need to reorder the data because well...

        //refactor how workspace are created because this sucks
        $this->data = $configuration->getData();
        $this->workspace = $workspace;
        $this->setImporters($configuration, $data);
        $this->setWorkspaceForImporter($workspace);

        if (!$isValidated) {
            $this->validate($data, false);
        }

        if ($importRoles) {
            $importedRoles = $this->getImporterByName('roles')->import($data['roles'], $workspace);
            $this->om->forceFlush();
        }

        foreach ($entityRoles as $key => $entityRole) {
            $importedRoles[$key] = $entityRole;
        }

        $this->log('Importing tools...');
        $tools = $this->getImporterByName('tools')->import($data['tools'], $workspace, $importedRoles, $root);
        $this->om->endFlushSuite();
    }

    /**
     * @param Configuration $configuration
     * @param User $owner
     * @param bool $isValidated
     *
     * @throws InvalidConfigurationException
     * @return SimpleWorkbolspace
     *
     * The template doesn't need to be validated anymore if
     *  - it comes from the self::import() function
     *  - we want to create a user from the default template (it should work no matter what)
     */
    public function createWorkspace(
        Configuration $configuration,
        User $owner,
        $isValidated = false
    )
    {
        $configuration->setOwner($owner);
        $data = $configuration->getData();
        $this->data = $data;
        $this->om->startFlushSuite();
        $this->setImporters($configuration, $data);

        if (!$isValidated) {
            $this->validate($data, false);
            $isValidated = true;
        }

        $workspace = new Workspace();
        $workspace->setName($configuration->getWorkspaceName());
        $workspace->setCode($configuration->getWorkspaceCode());
        $workspace->setDescription($configuration->getWorkspaceDescription());
        $workspace->setGuid($this->container->get('claroline.utilities.misc')->generateGuid());
        $workspace->setDisplayable($configuration->isDisplayable());
        $workspace->setSelfRegistration($configuration->getSelfRegistration());
        $workspace->setRegistrationValidation($configuration->getRegistrationValidation());
        $workspace->setSelfUnregistration($configuration->getSelfUnregistration());
        $date = new \Datetime(date('d-m-Y H:i'));
        $workspace->setCreationDate($date->getTimestamp());
        $this->om->persist($workspace);
        $this->om->flush();
        $this->log('Base workspace created...');

        //load roles
        $this->log('Importing roles...');
        $entityRoles = $this->getImporterByName('roles')->import($data['roles'], $workspace);
        //The manager role is required for every workspace
        $entityRoles['ROLE_WS_MANAGER'] = $this->container->get('claroline.manager.role_manager')->createWorkspaceRole(
            "ROLE_WS_MANAGER_{$workspace->getGuid()}",
            'manager',
            $workspace,
            true
        );

        $defaultZip = $this->container->getParameter('claroline.param.templates_directory') . 'default.zip';

        //batch import with default template shouldn't be flushed    
        if ($configuration->getArchive() !== $defaultZip) {
            $this->om->forceFlush();
        }

        $this->log('Roles imported...');
        $owner->addRole($entityRoles['ROLE_WS_MANAGER']);
        $this->om->persist($owner);

        //add base roles to the role array
        $pfRoles = $this->om->getRepository('ClarolineCoreBundle:Role')->findAllPlatformRoles();

        foreach ($pfRoles as $pfRole) {
            $entityRoles[$pfRole->getName()] = $pfRole;
        }

        $entityRoles['ROLE_ANONYMOUS'] = $this->om
            ->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ANONYMOUS');
        $entityRoles['ROLE_USER'] = $this->om
            ->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_USER');


        $dir = new Directory();
        $dir->setName($workspace->getName());
        $dir->setIsUploadDestination(true);

        $root = $this->container->get('claroline.manager.resource_manager')->create(
            $dir,
            $this->om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')->findOneByName('directory'),
            $owner,
            $workspace,
            null,
            null,
            array()
        );

        $this->log('Populating the workspace...');
        $this->populateWorkspace($workspace, $configuration, $root, $entityRoles, true, false);
        $this->container->get('claroline.manager.workspace_manager')->createWorkspace($workspace);

        if ($owner) {
            $this->log('Set the owner...');
            $workspace->setCreator($owner);
        }

        $this->om->endFlushSuite();
        $fs = new FileSystem();

        return $workspace;
    }

    //refactor how workspace are created because this sucks
    public function importRichText()
    {
        $this->log('Parsing rich texts...');
        //now we have to parse everything in case there is a rich text
        //rich texts must be located in the tools section
        $data = $this->data;
        //@todo remove the line for claroline v6
        $this->container->get('claroline.importer.rich_text_formatter')->setData($data);
        $this->container->get('claroline.importer.rich_text_formatter')->setWorkspace($this->workspace);

        foreach ($data['tools'] as $tool) {
            $importer = $this->getImporterByName($tool['tool']['type']);

            if (isset($tool['tool']['data']) && $importer instanceof RichTextInterface) {
                $data['data'] = $tool['tool']['data'];
                $importer->format($data);
            }
        }

        $this->om->flush();
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
     * Full workspace export
     */
    public function export(Workspace $workspace)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setListImporters($this->listImporters);
        }

        $data = [];
        $files = [];
        $data['roles'] = $this->getImporterByName('roles')->export($workspace, $files, null);
        $data['tools'] = $this->getImporterByName('tools')->export($workspace, $files, null);
        $_resManagerData = array();

        foreach ($data['tools'] as &$_tool) {
            if ($_tool['tool']['type'] === 'resource_manager') {
                $_resManagerData = &$_tool['tool'];
            }
        }

        //then we parse and replace the text, we also add missing files in $resManagerData
        $files = $this->container->get('claroline.importer.rich_text_formatter')
            ->setPlaceHolders($files, $_resManagerData);
        //throw new \Exception();
        //generate the archive in a temp dir
        $content = Yaml::dump($data, 10);
        //zip and returns the archive
        $archDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $archPath = $archDir . DIRECTORY_SEPARATOR . 'archive.zip';
        mkdir($archDir);
        $manifestPath = $archDir . DIRECTORY_SEPARATOR . 'manifest.yml';
        file_put_contents($manifestPath, $content);
        $archive = new \ZipArchive();
        $success = $archive->open($archPath, \ZipArchive::CREATE);

        if ($success === true) {
            $archive->addFile($manifestPath, 'manifest.yml');

            foreach ($files as $uid => $file) {
                $archive->addFile($file, $uid);
            }

            $archive->close();
        } else {
            throw new \Exception('Unable to create archive . ' . $archPath . ' (error ' . $success . ')');
        }

        return $archPath;
    }

    /**
     * Partial export for ressources
     */
    public function exportResources(Workspace $workspace, array $resourceNodes, $parseAndReplace = true)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setListImporters($this->listImporters);
        }
        $data = array();
        $files = array();
        $tool = array(
            'type' => 'resource_manager',
            'translation' => 'resource_manager',
            'roles' => array()
        );
        $resourceImporter = $this->container->get('claroline.tool.resource_manager_importer');
        $tool['data'] = $resourceImporter->exportResources($workspace, $resourceNodes, $files, null);
        $data['tools'] = array(0 => array('tool' => $tool));
        $_resManagerData = array();

        foreach ($data['tools'] as &$_tool) {
            if ($_tool['tool']['type'] === 'resource_manager') {
                $_resManagerData = &$_tool['tool'];
            }
        }

        if ($parseAndReplace) {
            $files = $this->container->get('claroline.importer.rich_text_formatter')
                ->setPlaceHolders($files, $_resManagerData);
        }

        //throw new \Exception();
        //generate the archive in a temp dir
        $content = Yaml::dump($data, 10);
        //zip and returns the archive
        $archDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid();
        $archPath = $archDir . DIRECTORY_SEPARATOR . 'archive.zip';
        mkdir($archDir);
        $manifestPath = $archDir . DIRECTORY_SEPARATOR . 'manifest.yml';
        file_put_contents($manifestPath, $content);
        $archive = new \ZipArchive();
        $success = $archive->open($archPath, \ZipArchive::CREATE);

        if ($success === true) {
            $archive->addFile($manifestPath, 'manifest.yml');

            foreach ($files as $uid => $file) {
                $archive->addFile($file, $uid);
            }

            $archive->close();
        } else {
            throw new \Exception('Unable to create archive . ' . $archPath . ' (error ' . $success . ')');
        }

        return $archPath;
    }

    /**
     * Inject the rootPath
     *
     * @param \Claroline\CoreBundle\Library\Workspace\Configuration $configuration
     * @param array $data
     * @param $isStrict
     */
    private function setImporters(Configuration $configuration, array $data)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setRootPath($configuration->getExtractPath());
            if ($owner = $configuration->getOwner()) {
                $importer->setOwner($owner);
            } else {
                $importer->setOwner($this->container->get('security.token_storage')->getToken()->getUser());
            }
            $importer->setConfiguration($data);
            $importer->setListImporters($this->listImporters);

            if ($this->logger) $importer->setLogger($this->logger);
        }
    }

    private function setWorkspaceForImporter(Workspace $workspace)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setWorkspace($workspace);
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
        $string .= $dumper->dump($this->getImporterByName('forum'));

        return $string;
    }

    /**
     * @param Configuration $configuration
     * @param User $owner
     */
    public function importResources(
        Configuration $configuration,
        User $owner,
        ResourceNode $directory
    )
    {
        $configuration->setOwner($owner);
        $data = $configuration->getData();
        $data = $this->reorderData($data);
        $this->data = $data;
        $this->workspace = $directory->getWorkspace();
        $this->om->startFlushSuite();
        $this->setImporters($configuration, $data);

        $resourceImporter = $this->container->get('claroline.tool.resource_manager_importer');

        if (isset($data['tools']) && is_array($data['tools'])) {

            foreach ($data['tools'] as $dataTool) {
                $tool = $dataTool['tool'];

                if ($tool['type'] === 'resource_manager') {
                    $resourceImporter->import(
                        $tool,
                        $this->workspace,
                        array(),
                        $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($directory),
                        false
                    );
                    break;
                }
            }
        }
        $this->om->endFlushSuite();
    }

    private function reorderData(array $data)
    {
        $resManager = null;

        foreach ($data['tools'] as $dataTool) {
            if ($dataTool['tool']['type'] === 'resource_manager') $resManager = $dataTool;
        }

        $priorities = array();

        //we currently only reorder resources...
        if (isset($resManager['tool']['data']['items'])) {
            foreach ($resManager['tool']['data']['items'] as $item) {
                $importer = $this->getImporterByName($item['item']['type']);
                if ($importer) $priorities[$importer->getPriority()][] = $item;
            }
        }

        ksort($priorities);
        $ordered = array();

        foreach ($priorities as $priority) {
            $ordered = array_merge($ordered, $priority);
        }

        foreach ($data['tools'] as &$dataTool) {
            if ($dataTool['tool']['type'] === 'resource_manager') {
                $dataTool['tool']['data']['items'] = $ordered;
            }
        }

        return $data;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
