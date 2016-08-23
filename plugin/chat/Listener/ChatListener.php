<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Listener;

use Claroline\CoreBundle\Event\OpenAdministrationToolEvent;
use Claroline\CoreBundle\Menu\ContactAdditionalActionEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service
 */
class ChatListener
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
    ) {
        $this->httpKernel = $httpKernel;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * @DI\Observe("administration_tool_claroline_chat_management_admin_tool")
     *
     * @param DisplayToolEvent $event
     */
    public function onChatManagementAdministrationToolOpen(OpenAdministrationToolEvent $event)
    {
        $params = [];
        $params['_controller'] = 'ClarolineChatBundle:AdminChat:adminChatManagement';
        $subRequest = $this->request->duplicate([], null, $params);
        $response = $this->httpKernel
            ->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        $event->setResponse($response);
        $event->stopPropagation();
    }

    /**
     * @DI\Observe("claroline_contact_additional_action")
     *
     * @param \Claroline\CoreBundle\Menu\ContactAdditionalActionEvent $event
     */
    public function onContactActionMenuRender(ContactAdditionalActionEvent $event)
    {
        $user = $event->getUser();
        $url = $this->router->generate('claro_chat_user', ['user' => $user->getId()]);

        $menu = $event->getMenu();
        $menu->addChild(
            $this->translator->trans('chat', [], 'chat'),
            ['uri' => $url]
        )->setExtra('icon', 'fa fa-comments-o')
        ->setExtra('display', 'new_small_window');

        return $menu;
    }
}
