<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Controller;

use Claroline\ChatBundle\Form\ChatUsersCreationType;
use Claroline\ChatBundle\Form\ChatConfigurationType;
use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('claroline_chat_management_admin_tool')")
 */
class AdminChatController extends Controller
{
    private $chatManager;
    private $formFactory;
    private $platformConfigHandler;
    private $request;
    private $router;

    /**
     * @DI\InjectParams({
     *     "chatManager"           = @DI\Inject("claroline.manager.chat_manager"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "router"                = @DI\Inject("router")
     * })
     */
    public function __construct(
        ChatManager $chatManager,
        FormFactory $formFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        RouterInterface $router
    )
    {
        $this->chatManager = $chatManager;
        $this->formFactory = $formFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
    }

    /**
     * @EXT\Route(
     *     "/admin/chat/management",
     *     name="claro_chat_admin_management",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminChatManagementAction()
    {

        return array();
    }

    /**
     * @EXT\Route(
     *     "/admin/chat/configure/form",
     *     name="claro_chat_admin_configure_form"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminChatConfigureFormAction()
    {
        $form = $this->formFactory->create(
            new ChatConfigurationType($this->platformConfigHandler)
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/admin/chat/configure",
     *     name="claro_chat_admin_configure"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function adminChatConfigureAction()
    {
        $formData = $this->request->get('chat_plugin_configuration_form');
        $host = $formData['host'];
        $port = $formData['port'];
        $this->platformConfigHandler->setParameters(
            array(
                'chat_xmpp_host' => $host,
                'chat_xmpp_port' => $port
            )
        );

        return new RedirectResponse(
            $this->router->generate('claro_chat_admin_management')
        );
    }

    /**
     * @EXT\Route(
     *     "/admin/chat/users/management/{show}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_chat_users_admin_management",
     *     defaults={"show"=0,"page"=1, "search"="", "max"=50, "orderedBy"="title","order"="ASC"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function adminchatUsersManagementAction(
        $show = 0,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'username',
        $order = 'ASC'
    )
    {
        $chatUsers = array();

        return array(
            'chatUsers' => $chatUsers,
            'show' => $show,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order
        );
    }

//    /**
//     * @EXT\Route(
//     *     "/chat/users/create/form",
//     *     name="claro_chat_users_create_form"
//     * )
//     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
//     * @EXT\Template()
//     */
//    public function chatUsersCreateFormAction()
//    {
//        $this->checkConfigurationAccess();
//        $blackList = array();
//        $form = $this->formFactory->create(new ChatUsersCreationType($blackList));
//
//        return array('form' => $form->createView());
//    }
//
//    /**
//     * @EXT\Route(
//     *     "/chat/users/create",
//     *     name="claro_chat_users_create"
//     * )
//     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
//     * @EXT\Template()
//     */
//    public function chatUsesrCreateAction()
//    {
//        $this->checkConfigurationAccess();
//        $blackList = array();
//        $form = $this->formFactory->create(new ChatUsersCreationType($blackList));
//        $form->handleRequest($this->request);
//
//        if ($form->isValid()) {
//            $users = $form->get('users')->getData();
//
//            return new JsonResponse('success', 200);
//        } else {
//
//            return array('form' => $form->createView());
//        }
//    }
}
