<?php

namespace Claroline\ActivityToolBundle\Listener;

use Claroline\CoreBundle\Library\Event\DisplayToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Service
 */
class ToolListener extends ContainerAware
{
    protected $container;

    /**
     * @DI\InjectParams({
     *     "container" = @DI\Inject("service_container")
     * })
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @DI\Observe("open_tool_desktop_claroline_activity_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDesktopOpen(DisplayToolEvent $event)
    {
        $token = $this->container->get('security.context')->getToken();
        $user = $token->getUser();
        $userRoles = $this->container->get('claroline.security.utilities')->getRoles($token);
        $em = $this->container->get('doctrine.orm.entity_manager');

        $criteria = array();
        $criteria['roots'] = array();
        $criteria['types'] = array('activity');
        $activityResources = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findByCriteria($criteria, $userRoles, true);

        $content = $this->container->get('templating')->render(
            'ClarolineActivityToolBundle::desktop_activities_list.html.twig',
            array()
        );
        $event->setContent($content);
        $event->stopPropagation();
    }
}