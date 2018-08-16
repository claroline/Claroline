<?php

namespace Claroline\CoreBundle\Listener\Administration;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;

// TODO : break me into one file per tool
// TODO : do not redirect to a controller, directly renders the tool template

/**
 * @DI\Service()
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
            '_controller' => 'ClarolineCoreBundle:Administration/Parameters:index',
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
            '_controller' => 'ClarolineCoreBundle:Administration/WorkspaceRegistration:registrationManagement',
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
            '_controller' => 'ClarolineCoreBundle:Administration/Tools:showTool',
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
