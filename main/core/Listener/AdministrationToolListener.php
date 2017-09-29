<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *  @DI\Service()
 */
class AdministrationToolListener
{
    private $request;
    private $httpKernel;

    /**
     * AdministrationToolListener constructor.
     *
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "httpKernel"   = @DI\Inject("http_kernel")
     * })
     *
     * @param RequestStack        $requestStack
     * @param HttpKernelInterface $httpKernel
     */
    public function __construct(
        RequestStack $requestStack,
        HttpKernelInterface $httpKernel)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * @DI\Observe("administration_tool_platform_parameters")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformParameters(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Parameters:index',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_user_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenUserManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Users:index',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_workspace_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenWorkspaceManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Workspace:index',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_registration_to_workspace")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenRegistrationToWorkspace(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\WorkspaceRegistration:registrationManagement',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_desktop_and_home")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function opDesktopAndHome(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\DesktopConfiguration:adminDesktopConfigMenu',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_desktop_tools")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenDesktopTools(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Tools:showTool',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_platform_logs")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformLogs(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Logs:logList',
            'page' => 1,
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_platform_analytics")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenPlatformAnalytics(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Analytics:analytics',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_roles_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenRolesManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Roles:index',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_widgets_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenWidgetsManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Widget:widgetsManagement',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_organization_management")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenOrganizationManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\Organization:index',
        ], $event);
    }

    /**
     * @DI\Observe("administration_tool_tasks_scheduling")
     *
     * @param OpenAdministrationToolEvent $event
     */
    public function onOpenScheduledTasksManagement(OpenAdministrationToolEvent $event)
    {
        $this->redirect([
            '_controller' => 'ClarolineCoreBundle:Administration\ScheduledTask:index',
        ], $event);
    }

    private function redirect($params, OpenAdministrationToolEvent $event)
    {
        $subRequest = $this->request->duplicate([], null, $params);

        $event->setResponse(
            $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $event->stopPropagation();
    }
}
