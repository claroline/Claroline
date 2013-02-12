<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Tool\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Workspace\Event;
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
            ->findWorkspaceRoot($workspace)
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
        $event = new Event();
        $formBuilder = $this->container->get('form.factory')->createBuilder('form', $event, array());
        $formBuilder
            ->add('title', 'text', array('required' => true))
            ->add(
                'end',
                'date',
                array(
                    'format' => 'dd-MM-yyyy',
                    'widget' => 'choice',
                    'data' => new \DateTime('now')
                )
            )
            ->add(
                'allDay',
                'checkbox',
                array(
                'label' => 'all day ?',
                )
            )
            ->add('description', 'textarea');
        $form = $formBuilder->getForm();

        return $this->container->get('templating')->render(
            'ClarolineCoreBundle:Tool:workspace/calendar/calendar.html.twig',
            array('workspace' => $workspace, 'form' => $form->createView())
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
            'ClarolineCoreBundle:Tool:desktop/calendar/calendar.html.twig',
            array('form' => $formBuilder-> getForm()-> createView())
        );
    }
}


