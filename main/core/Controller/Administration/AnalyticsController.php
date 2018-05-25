<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WidgetManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('platform_analytics')")
 */
class AnalyticsController extends Controller
{
    private $userManager;
    private $workspaceManager;
    private $widgetManager;
    private $formFactory;
    private $analyticsManager;
    private $request;
    private $analyticsTool;

    /**
     * @DI\InjectParams({
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "widgetManager"       = @DI\Inject("claroline.manager.widget_manager"),
     *     "formFactory"         = @DI\Inject("form.factory"),
     *     "analyticsManager"    = @DI\Inject("claroline.manager.analytics_manager"),
     *     "request"             = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        WidgetManager $widgetManager,
        FormFactory $formFactory,
        AnalyticsManager $analyticsManager,
        RequestStack $request
    ) {
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->widgetManager = $widgetManager;
        $this->formFactory = $formFactory;
        $this->analyticsManager = $analyticsManager;
        $this->request = $request->getMasterRequest();
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_analytics_show"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:administration/analytics:index.html.twig")
     *
     * Displays platform analytics home page
     *
     * @return array
     *
     * @throws \Exception
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @EXT\Route(
     *     "/item/{item}",
     *     name="claro_admin_analytics_other_details"
     * )
     */
    public function analyticsItemAction($item)
    {
        /** @var \Claroline\CoreBundle\Event\Analytics\PlatformContentItemDetailsEvent $event */
        $event = $this->get('claroline.event.event_dispatcher')->dispatch(
            'administration_analytics_platform_content_item_details_'.$item,
            'Analytics\PlatformContentItemDetails'
        );

        return new Response($event->getContent());
    }
}
