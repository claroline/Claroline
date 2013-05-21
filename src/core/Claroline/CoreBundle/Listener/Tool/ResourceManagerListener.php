<?php

namespace Claroline\CoreBundle\Listener\Tool;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use Claroline\CoreBundle\Library\Event\ExportToolEvent;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;

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
     *     "manager" = @DI\Inject("claroline.resource.manager"),
     *     "converter" = @DI\Inject("claroline.resource.converter"),
     *     "sc" = @DI\Inject("security.context"),
     *     "request" = @DI\Inject("request")
     * })
     */
    public function __construct($em, $ed, $templating, $manager, $converter, $sc, $request)
    {
        $this->em = $em;
        $this->ed = $ed;
        $this->templating = $templating;
        $this->manager = $manager;
        $this->converter = $converter;
        $this->sc = $sc;
        $this->request = $request;
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
        $breadcrumbsIds = $this->request->query->get('_breadcrumbs');
        if ($breadcrumbsIds != null) {
        $ancestors = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findResourcesByIds($breadcrumbsIds);
        $this->manager->checkAncestors($ancestors);
        } else {
            $ancestors = array();
        }
        $path = array();

        foreach ($ancestors as $ancestor) {
            $path[] = $this->converter->toArray($ancestor, $this->sc->getToken());
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
}
