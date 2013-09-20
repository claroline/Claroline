<?php

namespace Claroline\ActivityToolBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\SecurityContextInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ToolListener
{
    private $em;
    private $activityRepo;
    private $resourceManager;
    private $securityContext;
    private $templating;
    private $utils;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager"),
     *     "securityContext"    = @DI\Inject("security.context"),
     *     "templating"         = @DI\Inject("templating"),
     *     "utils"              = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct(
        EntityManager $em,
        ResourceManager $resourceManager,
        SecurityContextInterface $securityContext,
        TwigEngine $templating,
        Utilities $utils
    )
    {
        $this->em = $em;
        $this->resourceManager = $resourceManager;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->utils = $utils;
        $this->activityRepo = $em->getRepository('ClarolineCoreBundle:Resource\Activity');
    }

    /**
     * @DI\Observe("open_tool_desktop_claroline_activity_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDesktopOpen(DisplayToolEvent $event)
    {
        $datas = $this->fetchActivitiesDatas(true);

        $content = $this->templating->render(
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

        $content = $this->templating->render(
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
        $token = $this->securityContext->getToken();
        $userRoles = $this->utils->getRoles($token);

        $criteria = array();
        $criteria['roots'] = array();

        if (!$isDesktopTool) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $criteria['roots'][] = $root->getPath();
        }
        $criteria['types'] = array('activity');
        $nodes = $this->resourceManager->getByCriteria($criteria, $userRoles, true);

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
                $nodeWorkspaces = $this->resourceManager
                    ->getWorkspaceInfoByIds($activityNodesId);

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

            $activities = $this->activityRepo
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