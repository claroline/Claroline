<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\ExportToolEvent;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;

/**
 * @DI\Service
 */
class ResourceManagerListener
{

    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "templating" = @DI\Inject("templating"),
     *     "manager" = @DI\Inject("claroline.resource.manager")
     * })
     */
    public function __construct($em, $ed, $templating, $manager)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("open_tool_workspace_resource_manager")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayWorkspaceResouceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceWorkspace($event->getWorkspace()->getId()));
    }

    /**
     * @DI\Observe("open_tool_desktop_resource_manager")
     *
     * @param DisplayToolEvent $event
     */
    public function onDisplayDesktopResourceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceDesktop());
    }

    /**
     * Renders the resources page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return string
     *
     * @throws AccessDeniedHttpException
     */
    public function resourceWorkspace($workspaceId)
    {
        $workspace = $this->em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $directoryId = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($workspace)
            ->getId();
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findAll();

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resources.html.twig', array(
                'workspace' => $workspace,
                'directoryId' => $directoryId,
                'resourceTypes' => $resourceTypes
            )
        );
    }

    /**
     * Displays the resource manager.
     *
     * @return string
     */
    public function resourceDesktop()
    {
        $resourceTypes = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\desktop\resource_manager:resources.html.twig',
            array('resourceTypes' => $resourceTypes)
        );
    }

    /**
     * @DI\Observe("tool_resource_manager_from_template")
     *
     * @param ImportToolEvent $event
     * @throws Exception
     */
    public function onImportResource(ImportToolEvent $event)
    {
        $config = $event->getConfig();
        $root = $event->getRoot();
        $createdResources = array();
        $createdResources[$config['root_id']] = $root;
        $createdResources = $this->loadDirectories($config, $createdResources, $event->getRoot(), $event->getUser());
        $this->loadFiles($config, $createdResources, $event->getFiles(), $event->getRoot(), $event->getUser());
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
        $resourceRepo = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $root = $resourceRepo->findWorkspaceRoot($workspace);

        $roles = $this->em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($workspace);
        $config['root_id'] = $root->getId();

        $dirType = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');
        $children = $resourceRepo->findBy(array('parent' => $root, 'resourceType' => $dirType));

        foreach ($children as $child) {
            $newEvent = new ExportResourceTemplateEvent($child);
            $this->ed->dispatch("resource_directory_to_template", $newEvent);
            $dataChildren = $newEvent->getConfig();
            if ($dataChildren == null) {
                throw new \Exception('The event resource_directory_to_template did not return any config');
            }
            $config['directory'][] = $dataChildren;
        }

        $criteria = array();
        $criteria['roots'] = array($root->getName());
        $criteria['isExportable'] = true;
        $config['resources'] = array();
        $resources = $resourceRepo->findByCriteria($criteria);
        $addToArchive = array();

        foreach ($resources as $resource) {
            if ($resource['type'] !== 'directory') {
                $newEvent = new ExportResourceTemplateEvent($resourceRepo->find($resource['id']));
                $this->ed->dispatch("resource_{$resource['type']}_to_template", $newEvent);
                $dataResources = $newEvent->getConfig();

                if ($dataResources === null) {
                    throw new \Exception(
                        "The event resource_{$resource['type']}_to_template did not return any config"
                    );
                }

                foreach ($roles as $role) {
                    $perms = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                        ->findMaximumRights(array($role->getName()), $root);
                    $perms['canCreate'] = array();

                    $dataResources['perms'][rtrim(str_replace(range(0, 9), '', $role->getName()), '_')] = $perms;
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
     * @param array $config
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
     * @param type $key
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
     * @param array $config the config file.
     * @param array $createdResources the list of already created resource [$id] => [$entity]
     *
     * @return array
     */
    private function loadDirectories($config, $createdResources, $root, $user)
    {
        if (isset($config['directory'])) {
            foreach ($config['directory'] as $resource) {
                $newEvent = new ImportResourceTemplateEvent($resource, $root, $user);
                $this->ed->dispatch("resource_{$resource['type']}_from_template", $newEvent);

                $childResources = $newEvent->getCreatedResources();

                foreach ($childResources as $key => $value) {
                    $createdResources[$key] = $value;
                }
            }
        }

        return $createdResources;
    }

    private function loadFiles($config, $createdResources, $requiredFiles, $root, $user)
    {
        foreach ($config['resources'] as $resource) {

            $newEvent = new ImportResourceTemplateEvent($resource, $root, $user);
            $newEvent->setCreatedResources($createdResources);
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

            $newEvent->setFiles($fileContent);

            $this->ed->dispatch("resource_{$resource['type']}_from_template", $newEvent);
            $resourceEntity = $newEvent->getResource();

            if ($resourceEntity !== null) {
                $resourceEntity->setName($resource['name']);
                $this->manager->create(
                    $resourceEntity,
                    $createdResources[$resource['parent']],
                    $resource['type'],
                    $user,
                    $resource['perms']
                );
                $createdResources[$resource['id']] = $resourceEntity;
            } else {
                throw new \Exception(
                    "The event import_{$resource['type']}_template did not set" .
                    " any resource"
                );
            }
        }
    }
}
