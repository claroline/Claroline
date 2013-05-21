<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\AdminLogFilterType;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Form\GroupSettingsType;
use Claroline\CoreBundle\Form\PlatformParametersType;
use Claroline\CoreBundle\Library\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Library\Event\LogUserDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogGroupCreateEvent;
use Claroline\CoreBundle\Library\Event\LogGroupAddUserEvent;
use Claroline\CoreBundle\Library\Event\LogGroupRemoveUserEvent;
use Claroline\CoreBundle\Library\Event\LogGroupDeleteEvent;
use Claroline\CoreBundle\Library\Event\LogGroupUpdateEvent;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Claroline\CoreBundle\Repository\UserRepository;
use Symfony\Component\Form\FormError;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Exception\NotValidCurrentPageException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Claroline\CoreBundle\Form\DataTransformer\DateRangeToTextTransformer;
use Claroline\CoreBundle\Form\WorkspaceLogFilterType;


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
