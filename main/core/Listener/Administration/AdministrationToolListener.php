<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// TODO : break me into one file per tool
// TODO : do not redirect to a controller, directly renders the tool template

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
        HttpKernelInterface $httpKernel
    ) {
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
            '_controller' => 'ClarolineCoreBundle:administration\parameters:index',
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
            '_controller' => 'ClarolineCoreBundle:administration\workspace_registration:registration_management',
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
            '_controller' => 'ClarolineCoreBundle:administration\desktop_configuration:admin_desktop_config_menu',
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
            '_controller' => 'ClarolineCoreBundle:administration\tools:show_tool',
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
            '_controller' => 'ClarolineCoreBundle:administration\logs:log_list',
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
            '_controller' => 'ClarolineCoreBundle:administration\analytics:analytics',
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
            '_controller' => 'ClarolineCoreBundle:administration\widget:widgets_management',
        ], $event);
    }

    protected function redirect($params, OpenAdministrationToolEvent $event)
    {
        $subRequest = $this->request->duplicate([], null, $params);

        $event->setResponse(
            $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST)
        );
        $event->stopPropagation();
    }
}
