<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Display logs in desktop's tool.
 */
class DesktopLogController extends Controller
{
    /**
     * @Route(
     *     "/",
     *     name="claro_desktop_logs_show",
     *     requirements={"workspaceId" = "\d+"},
     *     defaults={"page" = 1}
     * )
     * @Route(
     *     "/{page}",
     *     name="claro_desktop_logs_show_paginated",
     *     requirements={"workspaceId" = "\d+", "page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     * @Method("GET")
     *
     * Displays logs list using filter parameteres and page number
     *
     * @param $page int The requested page number.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function logListAction($page)
    {
        return $this->render(
            'ClarolineCoreBundle:Tool/desktop/logs:log_list.html.twig',
            $this->get('claroline.log.manager')->getDesktopList($page)
        );
    }
}
