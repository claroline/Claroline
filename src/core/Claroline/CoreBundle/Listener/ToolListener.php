<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\ExportWorkspaceEvent;
use Claroline\CoreBundle\Library\Event\ImportWorkspaceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ExportWidgetConfigEvent;
use Claroline\CoreBundle\Library\Event\ImportWidgetConfigEvent;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Form\CalendarType;
use Symfony\Component\DependencyInjection\ContainerAware;

class ToolListener extends ContainerAware
{
    public function onDisplayWorkspaceResouceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceWorkspace($event->getWorkspace()->getId()));
    }

    public function onDisplayWorkspaceParameters(DisplayToolEvent $event)
    {
         $event->setContent($this->workspaceParameters($event->getWorkspace()->getId()));
    }

    public function onDisplayWorkspaceUserManagement(DisplayToolEvent $event)
    {
        $event->setContent($this->usersManagement($event->getWorkspace()->getId()));
    }

    public function onDisplayWorkspaceGroupManagement(DisplayToolEvent $event)
    {
        $event->setContent($this->groupsManagement($event->getWorkspace()->getId()));
    }

    public function onDisplayWorkspaceHome(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceHome($event->getWorkspace()->getId()));
    }

    public function onDisplayWorkspaceCalendar(DisplayToolEvent $event)
    {
        $event->setContent($this->workspaceCalendar($event->getWorkspace()->getId()));
    }

    public function onDisplayDesktopResourceManager(DisplayToolEvent $event)
    {
        $event->setContent($this->resourceDesktop());
    }

    public function onDisplayDesktopHome(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopHome());
    }

    public function onDisplayDesktopParameters(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopParameters());
    }

    public function onDisplayDesktopCalendar(DisplayToolEvent $event)
    {
        $event->setContent($this->desktopCalendar());
    }

    public function onExportHome(ExportWorkspaceEvent $event)
    {
        $home = array();
        $workspace = $event->getWorkspace();
        $configs = $this->container->get('claroline.widget.manager')
            ->generateWorkspaceDisplayConfig($workspace->getId());

        foreach ($configs as $config) {
            $widgetArray = array();
            $ed = $this->container->get('event_dispatcher');
            $newEvent = new ExportWidgetConfigEvent($config->getWidget(), $workspace, $event->getArchive());
            $ed->dispatch("widget_export_{$config->getWidget()->getName()}_configuration", $newEvent);
            $widgetArray['name'] = $config->getWidget()->getName();
            $widgetArray['is_visible'] = $config->isVisible();

            if ($newEvent->getConfig() != null) {
                $widgetArray['config'] = $newEvent->getConfig();
            }

            $perms[] = $widgetArray;
        }

        $home['widget'] = $perms;
        $event->setConfig($home);
    }

    //@todo: Optimize this if possible (it should be possible to reduce the number of dql requests)
    //because a new request is made each time to retrieve each resource right.
    public function onExportResource(ExportWorkspaceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $config = array();
        $workspace = $event->getWorkspace();
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $root = $resourceRepo->findWorkspaceRoot($workspace);

        $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($workspace);
        $config['root_id'] = $root->getId();

        $ed = $this->container->get('event_dispatcher');
        $dirType = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findOneByName('directory');
        $children = $resourceRepo->findBy(array('parent' => $root, 'resourceType' => $dirType));

        foreach ($children as $child) {
            $newEvent = new ExportResourceTemplateEvent($child, $event->getArchive());
            $ed->dispatch("export_directory_template", $newEvent);
            $dataChildren = $newEvent->getConfig();
            $config['directory'][] = $dataChildren;
        }

        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')->findAll();
        $criteria = array();

        foreach ($resourceTypes as $type) {
            if ($type->getName() !== 'directory') {
                $criteria['types'][] = $type->getName();
            }
        }

        $criteria['roots'] = array($root->getName());
        $config['resources'] = array();
        $resources = $resourceRepo->findUserResourcesByCriteria($criteria, null, true);

        foreach ($resources as $resource) {
            $newEvent = new ExportResourceTemplateEvent($resourceRepo->find($resource['id']) , $event->getArchive());
            $ed->dispatch("export_{$resource['type']}_template", $newEvent);
            $dataResources = $newEvent->getConfig();

            if ($dataResources !== null) {
                foreach ($roles as $role) {
                    $perms = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                        ->findMaximumRights(array($role->getName()), $root);
                    $perms['canCreate'] = array();

                    $dataResources['perms'][rtrim(str_replace(range(0, 9), '', $role->getName()), '_')] = $perms;
                }

                $dataResources['parent'] = $resource['parent_id'];
                $dataResources['id'] = $resource['id'];
                $dataResources['type'] = $resource['type'];
                $dataResources['name'] = $resource['name'];
                $config['resources'][] = $dataResources;

            }
        }

        $config['resources'] = $this->sortResources($config['resources']);
        $event->setConfig($config);
    }

    public function onImportHome(ImportWorkspaceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $config = $event->getConfig();
        $ed = $this->container->get('event_dispatcher');

        if (isset($config['widget'])) {
            foreach ($config['widget'] as $widgetConfig) {
                $widget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                    ->findOneByName($widgetConfig['name']);
                $parent = $em->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
                    ->findOneBy(array('widget' => $widget, 'parent' => null, 'isDesktop' => false));
                $displayConfig = new DisplayConfig();
                $displayConfig->setParent($parent);
                $displayConfig->setVisible($widgetConfig['is_visible']);
                $displayConfig->setWidget($widget);
                $displayConfig->setDesktop(false);
                $displayConfig->isLocked(true);
                $displayConfig->setWorkspace($event->getWorkspace());

                if (isset($widgetConfig['config'])) {
                    $newEvent = new ImportWidgetConfigEvent(
                        $widgetConfig['config'],
                        $event->getWorkspace(),
                        $event->getArchive()
                    );
                    $ed->dispatch("widget_import_{$widgetConfig['name']}_configuration", $newEvent);
                }

                $em->persist($displayConfig);
            }
        }
    }

    public function onImportResource(ImportWorkspaceEvent $event)
    {
        $manager = $this->container->get('claroline.resource.manager');
        $config = $event->getConfig();
        $root = $event->getRoot();
        $ed = $this->container->get('event_dispatcher');
        $createdResources = array();
        $createdResources[$config['root_id']] = $root;

        foreach ($config['directory'] as $resource) {
            $newEvent = new ImportResourceTemplateEvent($resource, $root, $event->getArchive(), $event->getUser());
            $ed->dispatch("import_{$resource['type']}_template", $newEvent);

            $childResources = $newEvent->getCreatedResources();

            foreach ($childResources as $key => $value) {
                $createdResources[$key] = $value;
            }
        }

        foreach ($config['resources'] as $resource) {
            $newEvent = new ImportResourceTemplateEvent($resource, $root, $event->getArchive(), $event->getUser());
            $newEvent->setCreatedResources($createdResources);
            $ed->dispatch("import_{$resource['type']}_template", $newEvent);
            $resourceEntity = $newEvent->getResource();

            if ($resourceEntity !== null) {
                $resourceEntity->setName($resource['name']);
                $manager->create(
                    $resourceEntity,
                    $createdResources[$resource['parent']],
                    $resource['type'],
                    $event->getUser(),
                    $resource['perms']
                );
                $createdResources[$resource['id']] = $resourceEntity;
            } else {
                throw new \Exception("The event import_{$resource['type']}_template did not set" .
                    " any resource");
            }
        }

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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $directoryId = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($workspace)
            ->getId();
        $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resources.html.twig', array(
                'workspace' => $workspace,
                'directoryId' => $directoryId,
                'resourceTypes' => $resourceTypes
            )
        );
    }

    /**
     * Renders the workspace properties page.
     *
     * @param integer $workspaceId
     *
     * @return string
     */
    public function workspaceParameters($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\workspace\parameters:parameters.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Renders the users management page with its layout.
     *
     * @param integer $workspaceId the workspace id
     *
     * @return string
     */
    public function usersManagement($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\workspace\user_management:user_management.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Renders the groups management page with its layout.
     *
     * @param integer $workspaceId the workspace id
     *
     * @return Response
     */
    public function groupsManagement($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\workspace\group_management:group_management.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Renders the home page with its layout.
     *
     * @param integer $workspaceId
     *
     * @return Response
     *
     * @throws AccessDeniedHttpException
     */
    public function workspaceHome($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\workspace\home:home.html.twig',
            array('workspace' => $workspace)
        );
    }

    /**
     * Displays the resource manager.
     *
     * @return string
     */
    public function resourceDesktop()
    {
        $resourceTypes = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool\desktop\resource_manager:resources.html.twig',
            array('resourceTypes' => $resourceTypes)
        );
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopHome()
    {
        return $this->container
            ->get('templating')
            ->render('ClarolineCoreBundle:Tool\desktop\home:info.html.twig');
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopParameters()
    {
        return $this->container
            ->get('templating')
            ->render('ClarolineCoreBundle:Tool\desktop\parameters:parameters.html.twig');
    }

    public function workspaceCalendar($workspaceId)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('ClarolineCoreBundle:Workspace\AbstractWorkspace')->find($workspaceId);
        $form = $this->container->get('form.factory')->create(new CalendarType());
        $listEvents = $em->getRepository('ClarolineCoreBundle:Event')->findByWorkspaceId($workspaceId, true);

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool/workspace/calendar:calendar.html.twig',
            array('workspace' => $workspace,
                'form' => $form->createView(),
                'listEvents' => $listEvents )
        );

    }

    public function desktopCalendar()
    {
        $event = new Event();
        $formBuilder = $this->container->get('form.factory')->createBuilder('form', $event, array());
        $formBuilder->add('title', 'text')
            ->add(
                'end',
                'date',
                array(
                    'format' => 'dd-MM-yyyy',
                    'widget' => 'choice',
                )
            )
            ->add(
                'allDay',
                'checkbox',
                array(
                    'label' => 'all day ?')
            )
            ->add('description', 'textarea');

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool/desktop/calendar:calendar.html.twig',
            array('form' => $formBuilder-> getForm()-> createView())
        );
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
                    $newConfig[$key+1] = $config[$tmpI];
                    unset($newConfig[$tmpI]);
                    $config = array_values($newConfig);
                    //the key has changed
                    $tmpI = $key+1;
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
        foreach($config as $key => $item) {
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
    private function shift(array $config, $key) {
        $size = count($config);
        $size--;
        for ($i = $size; $i >= $key; $i--) {
            $config[$i+1] = $config[$i];
        }
        unset($config[$key+1]);

        return $config;
    }
}


