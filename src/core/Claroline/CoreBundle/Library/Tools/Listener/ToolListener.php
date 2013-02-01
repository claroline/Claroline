<?php

namespace Claroline\CoreBundle\Library\Tools\Listener;

use Claroline\CoreBundle\Library\Tools\Event\DisplayToolEvent;
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
        $directoryId = $em->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($workspace)
            ->getId();
        $resourceTypes = $em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findBy(array('isVisible' => true));

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool:workspace\resource_manager\resources.html.twig', array(
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
            'ClarolineCoreBundle:Tool:workspace\parameters\parameters.html.twig',
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
            'ClarolineCoreBundle:Tool:workspace\user_management\user_management.html.twig',
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
            'ClarolineCoreBundle:Tool:workspace\group_management\group_management.html.twig',
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
            'ClarolineCoreBundle:Tool:workspace\home\home.html.twig',
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
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
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
        return $this->container->get('templating')->render('ClarolineCoreBundle:Tool\desktop\home:info.html.twig');
    }

    /**
     * Displays the Info desktop tab.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function desktopParameters()
    {
        return $this->container->get('templating')->render('ClarolineCoreBundle:Tool\desktop\properties:parameters.html.twig');
    }
}


