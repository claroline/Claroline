<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ActivityToolBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service
 */
class ToolListener
{
    private $em;
    private $activityRepo;
    private $evaluationRepo;
    private $resourceManager;
    private $tokenStorage;
    private $templating;
    private $utils;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "em"                 = @DI\Inject("doctrine.orm.entity_manager"),
     *     "resourceManager"    = @DI\Inject("claroline.manager.resource_manager"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "templating"         = @DI\Inject("templating"),
     *     "utils"              = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct(
        EntityManager $em,
        ResourceManager $resourceManager,
        TokenStorageInterface $tokenStorage,
        TwigEngine $templating,
        Utilities $utils
    ) {
        $this->em = $em;
        $this->resourceManager = $resourceManager;
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->utils = $utils;
        $this->activityRepo = $em->getRepository('ClarolineCoreBundle:Resource\Activity');
        $this->evaluationRepo = $em->getRepository('ClarolineCoreBundle:Activity\Evaluation');
    }

    /**
     * @DI\Observe("open_tool_desktop_claroline_activity_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDesktopOpen(DisplayToolEvent $event)
    {
        $data = $this->fetchActivitiesData(true);

        $content = $this->templating->render(
            'ClarolineActivityToolBundle::desktopActivityList.html.twig',
            array(
                'resourceInfos' => $data['resourceInfos'],
                'activityInfos' => $data['activityInfos'],
                'workspaceInfos' => $data['workspaceInfos'],
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
        $data = $this->fetchActivitiesData(false, $workspace);
        $content = $this->templating->render(
            'ClarolineActivityToolBundle::workspaceActivityList.html.twig',
            array(
                'workspace' => $workspace,
                'resourceInfos' => $data['resourceInfos'],
                'activityInfos' => $data['activityInfos'],
            )
        );
        $event->setContent($content);
        $event->stopPropagation();
    }

    public function fetchActivitiesData($isDesktopTool, Workspace $workspace = null)
    {
        $token = $this->tokenStorage->getToken();
        $userRoles = $this->utils->getRoles($token);

        $criteria = array();
        $criteria['roots'] = array();

        if (!$isDesktopTool) {
            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $criteria['roots'][] = $root->getPath();
        }

        $criteria['types'] = array('activity');
        $nodes = $this->resourceManager->getByCriteria($criteria, $userRoles);

        $activitiesData = array();
        $nodeInfo = array();
        $activityNodesId = array();
        $activityInfo = array();

        if ($isDesktopTool) {
            $workspaceInfo = array();
        }

        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            $activityNodesId[] = $nodeId;
            $nodeInfo[$nodeId] = $node;
        }

        if (count($activityNodesId) > 0) {
            if ($isDesktopTool) {
                $nodeWorkspaces = $this->resourceManager
                    ->getWorkspaceInfoByIds($activityNodesId);

                foreach ($nodeWorkspaces as $nodeWs) {
                    $code = $nodeWs['code'];

                    if (!isset($workspaceInfo[$code])) {
                        $workspaceInfo[$code] = array();
                        $workspaceInfo[$code]['code'] = $code;
                        $workspaceInfo[$code]['name'] = $nodeWs['name'];
                        $workspaceInfo[$code]['nodes'] = array();
                    }
                    $workspaceInfo[$code]['nodes'][] = $nodeWs['id'];
                }
            }

            $activities = $this->activityRepo
                ->findActivitiesByResourceNodeIds($activityNodesId);

            foreach ($activities as $activity) {
                $node = $activity->getResourceNode();
                $actNodeId = $node->getId();
                $activityInfo[$actNodeId] = array();
                $activityInfo[$actNodeId]['startDate'] = $node->getAccessibleFrom() instanceof \DateTime ?
                    $node->getAccessibleFrom()->format('Y-m-d H:i:s') :
                    '-';
                $activityInfo[$actNodeId]['endDate'] = $node->getAccessibleUntil() instanceof \DateTime ?
                    $node->getAccessibleUntil()->format('Y-m-d H:i:s') :
                    '-';
                $activityInfo[$actNodeId]['status'] = '-';

                if ($user = $token->getUser()) {
                    $evaluation = $this->evaluationRepo->findEvaluationByUserAndActivityParams(
                        $user,
                        $activity->getParameters()
                    );

                    if ($evaluation && $evaluation->getStatus()) {
                        $activityInfo[$actNodeId]['status'] = $evaluation->getStatus();
                    }
                }
            }
        }

        $activitiesData['resourceInfos'] = $nodeInfo;
        $activitiesData['activityInfos'] = $activityInfo;

        if ($isDesktopTool) {
            $activitiesData['workspaceInfos'] = $workspaceInfo;
        }

        return $activitiesData;
    }
}
