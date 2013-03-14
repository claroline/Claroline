<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\ExportWorkspaceEvent;
use Claroline\CoreBundle\Library\Event\ImportWorkspaceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceArrayEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceArrayEvent;
use Claroline\CoreBundle\Library\Event\ExportWidgetConfigEvent;
use Claroline\CoreBundle\Library\Event\ImportWidgetConfigEvent;
use Claroline\CoreBundle\Entity\Event;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\Widget\DisplayConfig;
use Claroline\CoreBundle\Entity\Role;
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

    public function onExportResource(ExportWorkspaceEvent $event)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $config = array();
        $workspace = $event->getWorkspace();
        $resourceRepo = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
        $root = $resourceRepo->findWorkspaceRoot($workspace);

        $roles = $em->getRepository('ClarolineCoreBundle:Role')->findByWorkspace($workspace);

        foreach ($roles as $role) {
            $perms = $em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
                ->findMaximumRights(array($role->getName()), $root);
            //temporary: no creation rights export for now
            $perms['canCreate'] = 1;
            $config['perms'][rtrim(str_replace(range(0, 9), '', $role->getName()), '_')] = $perms;
        }

        $ed = $this->container->get('event_dispatcher');
        $children = $resourceRepo->findChildren($root, array('ROLE_ADMIN'));

        foreach ($children as $child) {
            $newEvent = new ExportResourceArrayEvent($resourceRepo->find($child['id']), $event->getArchive());
            $ed->dispatch("export_{$child['type']}_array", $newEvent);
            $dataChildren = $newEvent->getConfig();
            if ($dataChildren !== null) {
                $config['resources'][] = $dataChildren;
            }
        }

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
        $em = $this->container->get('doctrine.orm.entity_manager');
        $roleRepo = $em->getRepository('ClarolineCoreBundle:Role');
        $config = $event->getConfig();
        $workspace = $event->getWorkspace();
        $root = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($workspace);

        foreach ($config['perms'] as $role => $permission) {
            $this->createDefaultsResourcesRights(
                $permission['canDelete'],
                $permission['canOpen'],
                $permission['canEdit'],
                $permission['canCopy'],
                $permission['canExport'],
                $permission['canCopy'],
                $roleRepo->findOneBy(array('name' => $role.'_'.$workspace->getId())),
                $root,
                $workspace
            );
        }

        $this->createDefaultsResourcesRights(
            false, false, false, false, false, false,
            $roleRepo->findOneBy(array('name' => 'ROLE_ANONYMOUS')),
            $root,
            $workspace
        );

        $this->createDefaultsResourcesRights(
            true, true, true, true, true, true,
            $roleRepo->findOneBy(array('name' => 'ROLE_ADMIN')),
            $root,
            $workspace
        );

        $em->flush();
        $ed = $this->container->get('event_dispatcher');

        if (isset($config['resources'])) {
            foreach ($config['resources'] as $resource) {
                $newEvent = new ImportResourceArrayEvent($resource, $root, $event->getArchive());
                $ed->dispatch("import_{$resource['type']}_array", $newEvent);
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
     * Create default permissions for a role and a resource.
     *
     * @param boolean $canDelete
     * @param boolean $canOpen
     * @param boolean $canEdit
     * @param boolean $canCopy
     * @param boolean $canExport
     * @param boolean $canCreate
     * @param \Claroline\CoreBundle\Entity\Role $role
     * @param \Claroline\CoreBundle\Entity\Resource\AbstractResource $resource
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceRights
     */
    private function createDefaultsResourcesRights(
        $canDelete,
        $canOpen,
        $canEdit,
        $canCopy,
        $canExport,
        $canCreate,
        Role $role,
        AbstractResource $resource,
        AbstractWorkspace $workspace
    )
    {
        $rights = new ResourceRights();
        $rights->setCanCopy($canCopy);
        $rights->setCanDelete($canDelete);
        $rights->setCanEdit($canEdit);
        $rights->setCanOpen($canOpen);
        $rights->setCanExport($canExport);
        $rights->setRole($role);
        $rights->setResource($resource);
        $rights->setWorkspace($workspace);
        $em = $this->container->get('doctrine.orm.entity_manager');

        if ($canCreate) {
            $resourceTypes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceType')
                ->findByIsVisible(true);
            $rights->setCreatableResourceTypes($resourceTypes);
        }

        $em->persist($rights);
    }
}


