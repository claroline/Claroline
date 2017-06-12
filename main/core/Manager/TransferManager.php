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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Transfert\Importer;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Claroline\CoreBundle\Library\Transfert\RichTextInterface;
use Claroline\CoreBundle\Library\Transfert\ToolRichTextInterface;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * @DI\Service("claroline.manager.transfer_manager")
 */
class TransferManager
{
    use LoggableTrait;

    private $listImporters;
    private $rootPath;
    private $om;
    private $container;
    private $data;
    private $workspace;
    private $templateDirectory;

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
        $this->templateDirectory = $container->getParameter('claroline.param.templates_directory');
        $this->data = [];
        $this->workspace = null;
    }

    public function addImporter(Importer $importer)
    {
        return $this->listImporters->add($importer);
    }

    /**
     * Import a workspace.
     */
    public function validate(array $data, $validateProperties = true)
    {
        $rolesImporter = $this->getImporterByName('roles');
        $toolsImporter = $this->getImporterByName('tools');
        $importer = $this->getImporterByName('workspace_properties');

        //properties
        if ($validateProperties) {
            if (isset($data['properties'])) {
                $properties['properties'] = $data['properties'];
                //get the validate return one day
                $importer->validate($properties);
            }
        }

        if (isset($data['roles'])) {
            $roles['roles'] = $data['roles'];
            //get the validate return one day
            $rolesImporter->validate($roles);
        }

        if (isset($data['tools'])) {
            $tools['tools'] = $data['tools'];
            $dataTools = $toolsImporter->validate($tools);
            $data['tools'] = $dataTools['tools'];
        }

        return $data;
    }

    /**
     * Populates a workspace content with the content of an zip archive. In other words, it ignores the
     * many properties of the configuration object and use an existing workspace as base.
     *
     * This will set the $this->data var
     * This will set the $this->workspace var
     *
     * @param Workspace $workspace
     * @param File      $template
     * @param Directory $root
     * @param array     $entityRoles
     * @param bool      $isValidated
     * @param bool      $importRoles
     */
    public function populateWorkspace(
        Workspace $workspace,
        array $data,
        File $template,
        Directory $root,
        array $entityRoles,
        $importRoles = true
    ) {
        return $this->populateWorkspaceFromTemplate($workspace, $data, $this->templateDirectory.$template->getBasename('.zip'), $root, $entityRoles, $importRoles);
    }

    /**
     * Populates a workspace content with the content of an zip archive. In other words, it ignores the
     * many properties of the configuration object and use an existing workspace as base.
     *
     * This will set the $this->data var
     * This will set the $this->workspace var
     *
     * @param Workspace $workspace
     * @param File      $template
     * @param Directory $root
     * @param array     $entityRoles
     * @param bool      $isValidated
     * @param bool      $importRoles
     */
    private function populateWorkspaceFromTemplate(
        Workspace $workspace,
        array $data,
        $template,
        Directory $root,
        array $entityRoles,
        $importRoles = true
    ) {
        $this->om->startFlushSuite();
        $this->setWorkspaceForImporter($workspace);
        if ($importRoles) {
            $importedRoles = $this->getImporterByName('roles')->import($data['roles'], $workspace);
            $this->om->forceFlush();
        }

        foreach ($entityRoles as $key => $entityRole) {
            $importedRoles[$key] = $entityRole;
        }

        $this->log('Importing tools...');
        $importerResult = $this->getImporterByName('tools')->import($data['tools'], $workspace, $importedRoles, $root);
        $this->om->endFlushSuite();
        //flush has to be forced unless it's a default template
        $defaults = [
            pathinfo(realpath($this->container->getParameter('claroline.param.default_template')), PATHINFO_DIRNAME),
            pathinfo(realpath($this->container->getParameter('claroline.param.personal_template')), PATHINFO_DIRNAME),
        ];

        if (!in_array($template, $defaults)) {
            $this->om->forceFlush();
        }

        $this->importRichText($workspace, $data, $importerResult['resource_manager']);

        if (isset($data['tabs']) && is_array($data['tabs'])) {
            $homeTabManager = $this->container->get('claroline.manager.home_tab_manager');
            $translator = $this->container->get('translator');

            foreach ($data['tabs'] as $tab) {
                if (isset($tab['tab']) && isset($tab['tab']['name'])) {
                    $homeTabManager->createHomeTab($translator->trans($tab['tab']['name'], [], 'platform'), $workspace);
                }
            }
        }
    }

    /**
     * @param File $template
     * @param User $owner
     * @param bool $isValidated
     *
     * @throws InvalidConfigurationException
     *
     * @return SimpleWorkbolspace
     *
     * The template doesn't need to be validated anymore if
     *  - it comes from the self::import() function
     *  - we want to create a user from the default template (it should work no matter what)
     */
    public function createWorkspace(
        Workspace $workspace,
        File $template,
        $isValidated = false
    ) {
        $data = $this->container->get('claroline.manager.workspace_manager')->getTemplateData($template, true);
        $workspace = $this->createWorkspaceFromData($workspace, $data, $this->templateDirectory.$template->getBasename('.zip'), $isValidated);
        $this->container->get('claroline.manager.workspace_manager')->removeTemplate($template);

        return $workspace;
    }

    /**
     * @param Workspace $workspace
     * @param string    $templatePath
     * @param bool      $isValidated
     *
     * @throws InvalidConfigurationException
     *
     * @return Workspace
     *
     * Create workspace from uncompressed template
     * The template doesn't need to be validated anymore if
     *  - it comes from the self::import() function
     *  - we want to create a user from the default template (it should work no matter what)
     */
    public function createWorkspaceFromTemplate(
        Workspace $workspace,
        $templatePath,
        $isValidated = false
    ) {
        $resolver = new Resolver($templatePath);
        $this->importData = $resolver->resolve();
        $data = $this->importData;
        $workspace = $this->createWorkspaceFromData($workspace, $data, $templatePath, $isValidated);
        $this->container->get('claroline.manager.workspace_manager')->removeTemplateDirectory($templatePath);

        return $workspace;
    }

    /**
     * @param Workspace $workspace
     * @param array     $data
     * @param string template
     * @param bool $isValidated
     *
     * @throws InvalidConfigurationException
     *
     * @return Workspace
     *
     * The template doesn't need to be validated anymore if
     *  - it comes from the self::import() function
     *  - we want to create a user from the default template (it should work no matter what)
     */
    private function createWorkspaceFromData(
        Workspace $workspace,
        array $data,
        $template,
        $isValidated = false
    ) {
        $data = $this->reorderData($data);

        if ($workspace->getCode() === null && isset($data['parameters'])) {
            $workspace->setCode($data['parameters']['code']);
        }

        if ($workspace->getName() === null && isset($data['parameters'])) {
            $workspace->setName($data['parameters']['name']);
        }

        if (isset($data['guid'])) {
            $workspace->setName($data['parameters']['guid']);
        }

        //just to be sure doctrine is ok before doing all the workspace
        $this->om->startFlushSuite();
        $data = $this->reorderData($data);
        $this->setImportersForTemplate($template, $workspace->getCreator(), $data);

        if (!$isValidated) {
            $data = $this->validate($data, false);
            $isValidated = true;
        }

        if (!$workspace->getGuid()) {
            $workspace->setGuid($this->container->get('claroline.utilities.misc')->generateGuid());
        }

        $date = new \Datetime(date('d-m-Y H:i'));
        $workspace->setCreationDate($date->getTimestamp());
        $this->om->persist($workspace);
        $this->om->flush();
        $this->log("Base {$workspace->getCode()} workspace created...");

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

        //flush has to be forced unless it's a default template
        $defaults = [
            pathinfo(realpath($this->container->getParameter('claroline.param.default_template')), PATHINFO_DIRNAME),
            pathinfo(realpath($this->container->getParameter('claroline.param.personal_template')), PATHINFO_DIRNAME),
        ];

        if (!in_array($template, $defaults)) {
            $this->om->forceFlush();
        }

        $owner = $workspace->getCreator();
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
            []
        );

        $this->log('Populating the workspace...');
        $this->populateWorkspaceFromTemplate($workspace, $data, $template, $root, $entityRoles, false);
        $this->container->get('claroline.manager.workspace_manager')->createWorkspace($workspace);
        $this->om->endFlushSuite();

        return $workspace;
    }

    public function importRichText(Workspace $workspace, array $data, array $resourceNodes = [])
    {
        $this->log('Parsing rich texts...');
        $this->container->get('claroline.importer.rich_text_formatter')->setData($data);
        $this->container->get('claroline.importer.rich_text_formatter')->setWorkspace($workspace);

        foreach ($data['tools'] as $tool) {
            $importer = $this->getImporterByName($tool['tool']['type']);

            if ($importer) {
                $importer->setWorkspace($workspace);

                if (isset($tool['tool']['data']) && ($importer instanceof RichTextInterface || $importer instanceof ToolRichTextInterface)) {
                    $data['data'] = $tool['tool']['data'];
                    $importer->format($data, $resourceNodes);
                }
            }
        }

        $this->om->flush();
    }

    private function getImporterByName($name)
    {
        foreach ($this->listImporters as $importer) {
            if ($importer->getName() === $name) {
                return $importer;
            }
        }

        return;
    }

    /**
     * Full workspace export.
     */
    public function export($workspace)
    {
        $this->log("Exporting {$workspace->getCode()}...");

        foreach ($this->listImporters as $importer) {
            $importer->setListImporters($this->listImporters);
        }

        $data = [];
        $files = [];
        $data['parameters']['code'] = $workspace->getCode();
        $data['parameters']['name'] = $workspace->getName();
        $data['parameters']['guid'] = $workspace->getGuid();
        $data['roles'] = $this->getImporterByName('roles')->export($workspace, $files, null);
        $data['tools'] = $this->getImporterByName('tools')->export($workspace, $files, null);
        $_resManagerData = [];

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
        $archDir = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir')
            .DIRECTORY_SEPARATOR.uniqid();
        $archPath = $archDir.DIRECTORY_SEPARATOR.'archive.zip';
        mkdir($archDir);
        $manifestPath = $archDir.DIRECTORY_SEPARATOR.'manifest.yml';
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
            throw new \Exception('Unable to create archive . '.$archPath.' (error '.$success.')');
        }

        $this->container->get('claroline.core_bundle.listener.kernel_terminate_listener')->addElementToRemove($archPath);

        return $archPath;
    }

    /**
     * Partial export for ressources.
     */
    public function exportResources(Workspace $workspace, array $resourceNodes, $parseAndReplace = true)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setListImporters($this->listImporters);
        }
        $data = [];
        $files = [];
        $tool = [
            'type' => 'resource_manager',
            'translation' => 'resource_manager',
            'roles' => [],
        ];
        $resourceImporter = $this->container->get('claroline.tool.resource_manager_importer');
        $tool['data'] = $resourceImporter->exportResources($workspace, $resourceNodes, $files, null);
        $data['tools'] = [0 => ['tool' => $tool]];
        $_resManagerData = [];

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
        $archDir = $this->container->get('claroline.config.platform_config_handler')->getParameter('tmp_dir')
            .DIRECTORY_SEPARATOR.uniqid();
        $archPath = $archDir.DIRECTORY_SEPARATOR.'archive.zip';
        mkdir($archDir);
        $manifestPath = $archDir.DIRECTORY_SEPARATOR.'manifest.yml';
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
            throw new \Exception('Unable to create archive . '.$archPath.' (error '.$success.')');
        }

        $this->container->get('claroline.core_bundle.listener.kernel_terminate_listener')->addElementToRemove($archPath);

        return $archPath;
    }

    /**
     * Inject the rootPath.
     *
     * @param File  $template
     * @param array $data
     */
    private function setImporters(File $template, User $owner, array $data)
    {
        $this->setImportersForTemplate($this->templateDirectory.$template->getBasename('.zip'), $owner, $data);
    }

    /**
     * Inject the rootPath.
     *
     * @param string $template
     * @param User   $owner
     * @param array  $data
     */
    private function setImportersForTemplate($template, User $owner, array $data)
    {
        foreach ($this->listImporters as $importer) {
            $importer->setRootPath($template);
            $importer->setOwner($owner);
            $importer->setConfiguration($data);
            $importer->setListImporters($this->listImporters);

            if ($this->logger) {
                $importer->setLogger($this->logger);
            }
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
     * @param File $template
     * @param User $owner
     */
    public function importResources(
        File $template,
        User $owner,
        ResourceNode $directory
    ) {
        $data = $this->container->get('claroline.manager.workspace_manager')->getTemplateData($template, true);
        $data = $this->reorderData($data);
        $workspace = $directory->getWorkspace();
        $this->om->startFlushSuite();
        $this->setImporters($template, $workspace->getCreator(), $data);

        $resourceImporter = $this->container->get('claroline.tool.resource_manager_importer');

        if (isset($data['tools']) && is_array($data['tools'])) {
            foreach ($data['tools'] as $dataTool) {
                $tool = $dataTool['tool'];

                if ($tool['type'] === 'resource_manager') {
                    $resourceNodes = $resourceImporter->import(
                        $tool,
                        $workspace,
                        [],
                        $this->container->get('claroline.manager.resource_manager')->getResourceFromNode($directory),
                        false
                    );
                    break;
                }
            }
        }
        $this->om->endFlushSuite();
        $this->importRichText($directory->getWorkspace(), $data, $resourceNodes);
        $this->container->get('claroline.manager.workspace_manager')->removeTemplate($template);
    }

    private function reorderData(array $data)
    {
        $resManager = null;

        foreach ($data['tools'] as $dataTool) {
            if ($dataTool['tool']['type'] === 'resource_manager') {
                $resManager = $dataTool;
            }
        }

        $priorities = [];

        //we currently only reorder resources...
        if (isset($resManager['tool']['data']['items'])) {
            foreach ($resManager['tool']['data']['items'] as $item) {
                $importer = $this->getImporterByName($item['item']['type']);
                if ($importer) {
                    $priorities[$importer->getPriority()][] = $item;
                }
            }
        }

        ksort($priorities);
        $ordered = [];

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

    public function getLogger()
    {
        return $this->logger;
    }
}
