<?php

namespace FormaLibre\SupportBundle\Listener;

use Claroline\CoreBundle\Event\DisplayToolEvent;
use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Event\PluginOptionsEvent;
use Claroline\CoreBundle\Menu\ExceptionActionEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class SupportListener
{
    private $httpKernel;
    private $request;
    private $router;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "httpKernel"   = @DI\Inject("http_kernel"),
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "router"       = @DI\Inject("router"),
     *     "translator"   = @DI\Inject("translator")
     * })
     */
    public function __construct(
        HttpKernelInterface $httpKernel,
        RequestStack $requestStack,
        UrlGeneratorInterface $router,
        TranslatorInterface $translator
    )
    {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("administration_tool_formalibre_support_management_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreSupportBundle:AdminSupport:adminSupportIndex';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("open_tool_desktop_formalibre_support_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onDesktopToolOpen(DisplayToolEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreSupportBundle:Support:supportIndex';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setContent($response->getContent());
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("plugin_options_supportbundle")
     *
     * @param DisplayToolEvent $event
     */
    public function onPluginOptionsOpen(PluginOptionsEvent $event)
    {
        $params = array();
        $params['_controller'] = 'FormaLibreSupportBundle:AdminSupport:pluginConfigureForm';
        $subRequest = $this->request->duplicate(array(), null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_exception_action")
     *
     * @param \Claroline\CoreBundle\Menu\ExceptionActionEvent $event
     */
    public function onExceptionActionMenuRender(ExceptionActionEvent $event)
    {
        $user = $event->getUser();
        $exceptionClassTemp = $event->getExceptionClass();
        $messageTemp = $event->getMessage();
        $fileTemp = $event->getFile();
        $lineTemp = $event->getLine();
        $urlTemp = $event->getUrl();
        $refererTemp = $event->getReferer();
        $exceptionClass = empty($exceptionClassTemp) ? '-' : $exceptionClassTemp;
        $message = empty($messageTemp) ? '-' : $messageTemp;
        $file = empty($fileTemp) ? '-' : $fileTemp;
        $line = empty($lineTemp) ? '-' : $lineTemp;
        $url = empty($urlTemp) ? '-' : $urlTemp;
        $referer = empty($refererTemp) ? '-' : $refererTemp;

        $route = $this->router->generate(
            'formalibre_ticket_from_issue_create_form',
            array(
                'user' => $user->getId(),
                'exceptionClass' => $exceptionClass,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'url' => $url,
                'referer' => $referer
            )
        );

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('create_ticket_for_issue', array(), 'support'),
            array('uri' => $route)
        )->setExtra('icon', 'fa fa-share')
        ->setExtra('display', 'modal_form');

        return $menu;
    }
}
