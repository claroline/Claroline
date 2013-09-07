<?php

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\Event\ExportToolEvent;
use Claroline\CoreBundle\Event\Event\ImportToolEvent;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\MaskManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ResourceManagerImportExportListener
{
    private $roleManager;
    private $em;
    private $ed;
    private $resourceManager;
    private $rightsManager;
    private $maskManager;

    /**
     * @DI\InjectParams({
     *     "em"              = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed"              = @DI\Inject("claroline.event.event_dispatcher"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"     = @DI\Inject("claroline.manager.role_manager"),
     *     "rightsManager"   = @DI\Inject("claroline.manager.rights_manager"),
     *     "maskManager"     = @DI\Inject("claroline.manager.mask_manager")
     * })
     */
    public function __construct(
        $em,
        $ed,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        RightsManager $rightsManager,
        MaskManager $maskManager
    )
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->rightsManager = $rightsManager;
        $this->maskManager = $maskManager;
    }

    /**
     * @DI\Observe("tool_resource_manager_from_template")
     *
     * @param  ImportToolEvent $event
     * @throws Exception
     */
    public function onImportResource(ImportToolEvent $event)
    {
        $config = $event->getConfig();
        $root = $event->getRoot();
        $createdResources = array();
        $createdResources[$config['root_id']] = $root;
        $createdResources = $this->loadDirectories(
            $config,
            $createdResources,
            $event->getRoot(),
            $event->getUser(),
            $event->getWorkspace(),
            $event->getRoles()
        );
        $this->loadFiles(
            $config,
            $createdResources,
            $event->getFilePaths(),
            $event->getRoot(),
            $event->getUser(),
            $event->getWorkspace(),
            $event->getRoles()
        );
    }

    /**
     * @DI\Observe("tool_resource_manager_to_template")
     *
     * @todo: Optimize this if possible (it should be possible to reduce the number of dql requests)
     * @todo: When exporting the resource, there should be only "exportable" resources. Therefore
     * the query builder must be updated because a new request is made each time to retrieve each
     * resource right.
     *
     * @param ExportToolEvent $event
     */
    public function onExportResource(ExportToolEvent $event)
    {
        $config = array();
        $workspace = $event->getWorkspace();
        $resourceRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceNode');
        $root = $this->resourceManager->getWorkspaceRoot($workspace);

        $roles = $this->roleManager->getRolesByWorkspace($workspace);
        $config['root_id'] = $root->getId();

        $dirType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');
        $children = $resourceRepo->findBy(array('parent' => $root, 'resourceType' => $dirType));

        foreach ($children as $child) {
            $newEvent = $this->ed->dispatch("resource_directory_to_template", 'ExportDirectoryTemplate', array($child));
            $dataChildren = $newEvent->getConfig();
            $config['directory'][] = $dataChildren;
        }

        $criteria = array();
        $criteria['roots'] = array($root->getName());
        $criteria['isExportable'] = true;
        $config['resources'] = array();
        $resources = $this->resourceManager->getByCriteria($criteria);
        $addToArchive = array();

        foreach ($resources as $resource) {
            if ($resource['type'] !== 'directory') {
                $newEvent = $this->ed->dispatch(
                    "resource_{$resource['type']}_to_template",
                    'ExportResourceTemplate',
                    array($this->resourceManager->getResourceFromNode($this->resourceManager->getNode($resource['id'])))
                );
                $dataResources = $newEvent->getConfig();

                if ($dataResources === null) {
                    throw new \Exception(
                        "The event resource_{$resource['type']}_to_template did not return any config"
                    );
                }

                foreach ($roles as $role) {
                    $mask = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                        ->findMaximumRights(array($role->getName()), $root);
                    $perms = $this->maskManager->decodeMask($mask, $this->resourceManager->getResourceTypeByName($resource['type']));
                    $perms['create'] = array();

                    $dataResources['perms'][$this->roleManager->getRoleBaseName($role->getName())] = $perms;
                }

                $dataResources['parent'] = $resource['parent_id'];
                $dataResources['id'] = $resource['id'];
                $dataResources['type'] = $resource['type'];
                $dataResources['name'] = $resource['name'];
                $requiredFiles = array();

                foreach ($newEvent->getFiles() as $item) {
                    $addToArchive[] = $item;
                    $requiredFiles[] = $item['archive_path'];
                    $dataResources['files'] = $requiredFiles;
                }

                $config['resources'][] = $dataResources;
            }
        }

        $event->setFiles($addToArchive);
        $config['resources'] = $this->sortResources($config['resources']);
        $event->setConfig($config);
    }

        /**
     * Reorder the $config array. The resources included in an activity must be placed before
     * the activity itself.
     * I don't guarantee it'll work every time.
     *
     * @param array $config
     *
     * @return array $config
     */
    private function sortResources(array $config)
    {
        //step 1: activities go after everything else
        for ($i = 0, $countActivity = 0, $size = count($config); $i < $size; $i++) {
            $item = $config[$i];

            if ($item['type'] === 'activity') {
                array_push($config, $config[$i]);
                unset($config[$i]);
                $countActivity++;
            }
        }

        //step 2: sort activities
        $config = array_values($config);
        $newConfig = $config;

        for ($i = $size - $countActivity; $i < $size; $i++) {

            //what does the activity need
            foreach ($config[$i]['resources'] as $item) {
                $key = $this->searchConfigById($config, $item['id']);
                //current key of the moving activity
                $tmpI = $i;

                //if the researched key is after the activity
                if ($key > $tmpI) {
                    //make some room to move the activity after the researched key
                    $newConfig = $this->shift($config, $key);
                    //moving the activity
                    $newConfig[$key + 1] = $config[$tmpI];
                    unset($newConfig[$tmpI]);
                    $config = array_values($newConfig);
                    //the key has changed
                    $tmpI = $key + 1;
                }
            }
        }

        return $config;
    }

    /**
     * Search a resource by Id in the $config array (for the template export)
     *
     * @param array   $config
     * @param integer $id
     *
     * @return the key wich was found for the resourceId.
     */
    private function searchConfigById(array $config, $id)
    {
        foreach ($config as $key => $item) {
            if ($item['id'] === $id) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Shift by one to the right the element on an array after the key $key.
     *
     * @param array $config
     * @param type  $key
     */
    private function shift(array $config, $key)
    {
        $size = count($config);
        $size--;
        for ($i = $size; $i >= $key; $i--) {
            $config[$i + 1] = $config[$i];
        }
        unset($config[$key + 1]);

        return $config;
    }

    /**
     * Load directories from a template config file.
     *
     * @param array $config           the config file.
     * @param array $createdResources the list of already created resource [$id] => [$entity]
     *
     * @return array
     */
    private function loadDirectories(
        $config,
        $createdResources,
        $root,
        $user,
        AbstractWorkspace $workspace,
        array $roles
    )
    {
        if (isset($config['directory'])) {
            foreach ($config['directory'] as $resource) {
                $newEvent = $this->ed->dispatch(
                    "resource_{$resource['type']}_from_template",
                    'ImportResourceTemplate',
                    array($resource, $root, $user, $workspace, $roles)
                );

                $childResources = $newEvent->getCreatedResources();

                foreach ($childResources as $key => $value) {
                    $createdResources[$key] = $value;
                }
            }
        }

        return $createdResources;
    }

    private function loadFiles(
        $config,
        $createdResources,
        $requiredFiles,
        $root,
        $user,
        AbstractWorkspace $workspace,
        array $roles
    )
    {
        foreach ($config['resources'] as $resource) {

            $fileContent = array();

            if (isset($resource['files'])) {
                $files = $resource['files'];

                foreach ($files as $file) {
                    foreach ($requiredFiles as $requiredFile) {
                        if ($file === pathinfo($requiredFile, PATHINFO_BASENAME)) {
                            $fileContent[] = $requiredFile;
                        }
                    }
                }
            }

            $newEvent = $this->ed->dispatch(
                "resource_{$resource['type']}_from_template",
                'ImportResourceTemplate',
                array($resource, $root, $user, $workspace, $roles, $createdResources, $fileContent)
            );

            $resourceEntity = $newEvent->getResource();
            $resourceEntity->setName($resource['name']);
            $this->resourceManager->create(
                $resourceEntity,
                $this->resourceManager->getResourceTypeByName($resource['type']),
                $user,
                $workspace,
                $createdResources[$resource['parent']],
                null,
                $this->rightsManager->addRolesToPermsArray($roles, $resource['perms'])
            );

            $createdResources[$resource['id']] = $resourceEntity;
        }
    }
}

