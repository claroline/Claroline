<?php

namespace Claroline\ActivityToolBundle\Listener;

use Claroline\CoreBundle\Event\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;
use JMS\DiExtraBundle\Annotation as DI;

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
            'ClarolineActivityToolBundle::desktopActivityList.html.twig',
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
            'ClarolineActivityToolBundle::workspaceActivityList.html.twig',
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
            $root = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                ->findWorkspaceRoot($workspace);
            $criteria['roots'][] = $root->getPath();
        }
        $criteria['types'] = array('activity');
        $nodes = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
            ->findByCriteria($criteria, $userRoles, true);

        $activitiesDatas = array();
        $nodeInfos = array();
        $activityNodesId = array();
        $activityInfos = array();

        if ($isDesktopTool) {
            $workspaceInfos = array();
        }

        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            $activityNodesId[] = $nodeId;
            $nodeInfos[$nodeId] = $node;
        }

        if (count($activityNodesId) > 0) {
            if ($isDesktopTool) {
                $nodeWorkspaces = $em->getRepository('ClarolineCoreBundle:Resource\ResourceNode')
                    ->findWorkspaceInfoByIds($activityNodesId);

                foreach ($nodeWorkspaces as $nodeWs) {
                    $code = $nodeWs['code'];

                    if (!isset($workspaceInfos[$code])) {
                        $workspaceInfos[$code] = array();
                        $workspaceInfos[$code]['code'] = $code;
                        $workspaceInfos[$code]['name'] = $nodeWs['name'];
                        $workspaceInfos[$code]['nodes'] = array();
                    }
                    $workspaceInfos[$code]['nodes'][] = $nodeWs['id'];
                }
            }

            $activities = $em->getRepository('ClarolineCoreBundle:Resource\Activity')
                ->findActivitiesByNodeIds($activityNodesId);

            foreach ($activities as $activity) {
                $actNodeId = $activity['nodeId'];
                $activityInfos[$actNodeId] = array();
                $activityInfos[$actNodeId]['instructions'] = $activity['instructions'];
                $activityInfos[$actNodeId]['startDate'] = ($activity['startDate'] instanceof \DateTime) ?
                    $activity['startDate']->format('Y-m-d H:i:s') : '-';
                $activityInfos[$actNodeId]['endDate'] = ($activity['endDate'] instanceof \DateTime) ?
                    $activity['endDate']->format('Y-m-d H:i:s') : '-';
            }
        }

        $activitiesDatas['resourceInfos'] = $nodeInfos;
        $activitiesDatas['activityInfos'] = $activityInfos;

        if ($isDesktopTool) {
            $activitiesDatas['workspaceInfos'] = $workspaceInfos;
        }

        return $activitiesDatas;
    }
}