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
        $this->redirect($params, $event);
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
        $this->redirect($params, $event);
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
        $this->redirect($params, $event);
    }

    /**
     * @DI\Observe("administration_tool_registration_to_workspace")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenRegistrationToWorkspace(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\WorkspaceRegistration:registrationManagement';
        $params['search'] = '';
        $this->redirect($params, $event);
    }

    /**
     * @DI\Observe("administration_tool_platform_packages")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformPackages(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Package:list';
        $this->redirect($params, $event);
    }


    /**
     * @DI\Observe("administration_tool_desktop_and_home")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function opDesktopAndHome(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\DesktopConfiguration:adminDesktopConfigMenu';
        $this->redirect($params, $event);
    }


    /**
     * @DI\Observe("administration_tool_desktop_tools")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenDesktopTools(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Tools:showTool';
        $this->redirect($params, $event);
    }


    /**
     * @DI\Observe("administration_tool_platform_logs")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformLogs(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Logs:logList';
        $params['page'] = 1;
        $this->redirect($params, $event);
    }


    /**
     * @DI\Observe("administration_tool_platform_analytics")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformAnalytics(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Analytics:analytics';
        $this->redirect($params, $event);
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
        $this->redirect($params, $event);
    }

    /**
     * @DI\Observe("administration_tool_widgets_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenWidgetsManagement(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Widget:widgetsManagement';
        $this->redirect($params, $event);
    }

    /**
     * @DI\Observe("administration_tool_organization_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenOrganizationManagement(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'ClarolineCoreBundle:Administration\Organization:index';
        $this->redirect($params, $event);
    }

    protected function redirect($params, $event)
    {
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }
}
