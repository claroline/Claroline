<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Event\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\Event\ConfigureWorkspaceToolEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @DI\Service(scope="request")
 */
class ResourceManagerListener
{
    /**
     * @DI\InjectParams({
     *     "em" = @DI\Inject("doctrine.orm.entity_manager"),
     *     "ed" = @DI\Inject("event_dispatcher"),
     *     "templating" = @DI\Inject("templating"),
     *     "manager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "converter" = @DI\Inject("claroline.resource.converter"),
     *     "sc" = @DI\Inject("security.context"),
     *     "request" = @DI\Inject("request"),
     *     "organizer" = @DI\Inject("claroline.workspace.organizer")
     * })
     */
    public function __construct($em, $ed, $templating, $manager, $converter, $sc, $request, $organizer)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->sc = $sc;
        $this->request = $request;
        $this->organizer = $organizer;
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
     * @DI\Observe("configure_workspace_tool_resource_manager")
     *
     * @param ConfigureWorkspaceToolEvent $event
     */
    public function onDisplayWorkspaceResourceConfiguration(ConfigureWorkspaceToolEvent $event)
    {
        $event->setContent($this->workspaceResourceRightsForm($event->getWorkspace()));
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
     */
    public function resourceWorkspace($workspaceId)
    {
        $breadcrumbsIds = $this->request->query->get('_breadcrumbs');
        if ($breadcrumbsIds != null) {
            $ancestors = $this->manager->getByIds($breadcrumbsIds);
            if (!$this->manager->isPathValid($ancestors)) {
                throw new \Exception('Breadcrumbs invalid');
            };
        } else {
            $ancestors = array();
        }
        $path = array();

        foreach ($ancestors as $ancestor) {
            $path[] = $this->manager->toArray($ancestor);
        }

        $jsonPath = json_encode($path);

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
                'resourceTypes' => $resourceTypes,
                'jsonPath' => $jsonPath
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

    private function workspaceResourceRightsForm(AbstractWorkspace $workspace)
    {
        if (!$this->sc->isGranted('parameters', $workspace)) {
            throw new AccessDeniedException();
        }
        $resource = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->findWorkspaceRoot($workspace);
        $roleRights = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceRights')
            ->findNonAdminRights($resource);

        $datas = $this->organizer->getDatasForWorkspaceList(true);

        return $this->templating->render(
            'ClarolineCoreBundle:Tool\workspace\resource_manager:resourcesRights.html.twig',
            array(
                'workspace' => $workspace,
                'resource' => $resource,
                'roleRights' => $roleRights,
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'workspaceRoles' => $datas['workspaceRoles']
            )
        );
    }
}
