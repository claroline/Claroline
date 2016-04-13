<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Log\Tool;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Display logs in workspace's tool.
 */
class WorkspaceController extends Controller
{
    /**
     * @EXT\Route(
     *     "/{workspaceId}/tool/logs",
     *     name="claro_workspace_logs_show",
     *     requirements={"workspaceId" = "\d+"},
     *     defaults={"page" = 1}
     * )
     * @EXT\Route(
     *     "/{workspaceId}/tool/logs{page}",
     *     name="claro_workspace_logs_show_paginated",
     *     requirements={"workspaceId" = "\d+", "page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool/workspace/logs:logList.html.twig")
     *
     * Displays logs list using filter parameteres and page number
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param $page int The requested page number.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return Response
     */
    public function logListAction(Workspace $workspace, $page)
    {
        if (!$this->get('security.authorization_checker')->isGranted('logs', $workspace)) {
            throw new AccessDeniedException();
        }

        return $this->get('claroline.log.manager')->getWorkspaceList($workspace, $page);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tool/logs/user",
     *     name="claro_workspace_logs_by_user_show",
     *     requirements={"workspaceId" = "\d+"},
     *     defaults={"page" = 1}
     * )
     * @EXT\Route(
     *     "/{workspaceId}/tool/logs/user{page}",
     *     name="claro_workspace_logs_by_user_show_paginated",
     *     requirements={"workspaceId" = "\d+", "page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Tool/workspace/logs:logByUser.html.twig")
     *
     * Displays logs list using filter parameteres and page number
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @param $page int The requested page number.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return Response
     */
    public function logByUserAction(Workspace $workspace, $page)
    {
        if (!$this->get('security.authorization_checker')->isGranted('logs', $workspace)) {
            throw new AccessDeniedException();
        }

        return $this->get('claroline.log.manager')->countByUserWorkspaceList($workspace, $page);
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/tool/logs/user/csv",
     *     name="claro_workspace_logs_by_user_csv",
     *     requirements={"workspaceId" = "\d+"}
     * )
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return Response
     */
    public function logByUserCSVAction(Workspace $workspace)
    {
        if (!$this->get('security.authorization_checker')->isGranted('logs', $workspace)) {
            throw new AccessDeniedException();
        }

        $logManager = $this->get('claroline.log.manager');

        $response = new StreamedResponse(function () use ($logManager,$workspace) {

            $results = $logManager->countByUserListForCSV('workspace', $workspace);
            $handle = fopen('php://output', 'w+');
            while (false !== ($row = $results->next())) {
                // add a line in the csv file. You need to implement a toArray() method
                // to transform your object into an array
                fputcsv($handle, array($row[$results->key()]['name'], $row[$results->key()]['actions']));
            }

            fclose($handle);
        });
        $dateStr = date('YmdHis');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename="user_actions_'.$dateStr.'.csv"');

        return $response;
    }
}
