<?php

namespace Claroline\ActivityToolBundle\Listener;

use Claroline\CoreBundle\Event\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
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
        $datas = $this->fetchActivitiesDatas(true);

        $content = $this->container->get('templating')->render(
            'ClarolineActivityToolBundle::desktop_activity_list.html.twig',
            array(
                'resourceInfos' => $datas['resourceInfos'],
                'activityInfos' => $datas['activityInfos'],
                'workspaceInfos' => $datas['workspaceInfos']
            )
        );
        $event->setContent($content);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_workspace_claroline_activity_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onWorkspaceOpen(DisplayToolEvent $event)
    {
        $workspace = $event->getWorkspace();
        $datas = $this->fetchActivitiesDatas(false, $workspace);

        $content = $this->container->get('templating')->render(
            'ClarolineActivityToolBundle::workspace_activity_list.html.twig',
            array(
                'workspace' => $workspace,
                'resourceInfos' => $datas['resourceInfos'],
                'activityInfos' => $datas['activityInfos']
            )
        );
        $event->setContent($content);
        $event->stopPropagation();
    }

    public function fetchActivitiesDatas($isDesktopTool, AbstractWorkspace $workspace = null)
    {
        $token = $this->container->get('security.context')->getToken();
        $userRoles = $this->container->get('claroline.security.utilities')->getRoles($token);
        $em = $this->container->get('doctrine.orm.entity_manager');

        $criteria = array();
        $criteria['roots'] = array();

        if (!$isDesktopTool) {
            $root = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                ->findWorkspaceRoot($workspace);
            $criteria['roots'][] = $root->getPath();
        }
        $criteria['types'] = array('activity');
        $resources = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findByCriteria($criteria, $userRoles, true);

        $activitiesDatas = array();
        $resourceInfos = array();
        $activitiesId = array();
        $activityInfos = array();

        if ($isDesktopTool) {
            $workspaceInfos = array();
        }

        foreach ($resources as $resource) {
            $resourceId = $resource['id'];
            $activitiesId[] = $resourceId;
            $resourceInfos[$resourceId] = $resource;
        }

        if (count($activitiesId) > 0) {
            if ($isDesktopTool) {
                $resourcesWorkspaces = $em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
                    ->findWorkspaceInfoByIds($activitiesId);

                foreach ($resourcesWorkspaces as $resWs) {
                    $code = $resWs['code'];

                    if (!isset($workspaceInfos[$code])) {
                        $workspaceInfos[$code] = array();
                        $workspaceInfos[$code]['code'] = $code;
                        $workspaceInfos[$code]['name'] = $resWs['name'];
                        $workspaceInfos[$code]['resources'] = array();
                    }
                    $workspaceInfos[$code]['resources'][] = $resWs['id'];
                }
            }

            $activities = $em->getRepository('ClarolineCoreBundle:Resource\Activity')
                ->findActivitiesByIds($activitiesId);

            foreach ($activities as $activity) {
                $actId = $activity['id'];
                $activityInfos[$actId] = array();
                $activityInfos[$actId]['instructions'] = $activity['instructions'];
                $activityInfos[$actId]['startDate'] = ($activity['startDate'] instanceof \DateTime) ?
                    $activity['startDate']->format('Y-m-d H:i:s') : '-';
                $activityInfos[$actId]['endDate'] = ($activity['endDate'] instanceof \DateTime) ?
                    $activity['endDate']->format('Y-m-d H:i:s') : '-';
            }
        }

        $activitiesDatas['resourceInfos'] = $resourceInfos;
        $activitiesDatas['activityInfos'] = $activityInfos;

        if ($isDesktopTool) {
            $activitiesDatas['workspaceInfos'] = $workspaceInfos;
        }

        return $activitiesDatas;
    }
}