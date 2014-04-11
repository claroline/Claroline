<?php

namespace Claroline\CoreBundle\Listener;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @DI\Service()
 */
class AdministrationToolListener
{
    private $request;
    private $httpKernel;

    /**
     * @DI\InjectParams({
     *     "requestStack"   = @DI\Inject("request_stack"),
     *     "httpKernel"     = @DI\Inject("http_kernel")
     * })
     */
    public function __construct(RequestStack $requestStack, HttpKernelInterface $httpKernel)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("administration_tool_user_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenUserManagement(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Users:index';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_platform_parameters")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformParameters(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Parameters:index';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_workspace_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenWorkspaceManagement(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Workspaces:management';
        $params['page'] = 1;
        $params['search'] = '';
        $params['max'] = 50;
        $params['direction'] = 'ASC';
        $params['order'] = 'id';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_badges_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenBadgesManagement(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['badgePage'] = 1;
        $params['claimPage'] = 1;
        $params['_controller'] = 'ClarolineCoreBundle:Badge/Admin:list';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_registration_to_workspace")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenRegistrationToWorkspace(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration:registrationManagement';
        $params['search'] = '';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_platform_plugins")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformPlugins(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration:pluginList';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }


    /**
     * @DI\Observe("administration_tool_home_tabs")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenHomeTabs(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:AdministrationHomeTab:adminHomeTabsConfigMenu';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }


    /**
     * @DI\Observe("administration_tool_desktop_tools")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenDesktopTools(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Tool\AdministrationTool:showTool';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }


    /**
     * @DI\Observe("administration_tool_platform_logs")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformLogs(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration:logList';
        $params['page'] = 1;
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }


    /**
     * @DI\Observe("administration_tool_platform_analytics")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformAnalytics(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration:analytics';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("administration_tool_roles_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenRolesManagement(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Roles:index';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
} 