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

use Claroline\ChatBundle\Form\PluginConfigurationType;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

require_once __DIR__ . "/../Library/xmpphp-master/XMPPHP/XMPP.php";
require_once __DIR__ . "/../Library/xmpphp-master/XMPPHP/Log.php";
require_once __DIR__ . "/../Library/xmpphp-master/XMPPHP/Exception.php";
use \XMPPHP_XMPP;
use \XMPPHP_Log;
use \XMPPHP_Exception;

class ChatController extends Controller
{
    private $authorization;
    private $formFactory;
    private $platformConfigHandler;
    private $request;
    private $toolManager;

    /**
     * @DI\InjectParams({
     *     "authorization"         = @DI\Inject("security.authorization_checker"),
     *     "formFactory"           = @DI\Inject("form.factory"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "requestStack"          = @DI\Inject("request_stack"),
     *     "toolManager"           = @DI\Inject("claroline.manager.tool_manager")
     * })
     */
    public function __construct(
        AuthorizationCheckerInterface $authorization,
        FormFactory $formFactory,
        PlatformConfigurationHandler $platformConfigHandler,
        RequestStack $requestStack,
        ToolManager $toolManager
    )
    {
        $this->authorization = $authorization;
        $this->formFactory = $formFactory;
        $this->platformConfigHandler = $platformConfigHandler;
        $this->request = $requestStack->getCurrentRequest();
        $this->toolManager = $toolManager;
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/chat",
     *     name="claro_chat_user",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function userChatAction(User $authenticatedUser, User $user)
    {
        $xmppHost = $this->platformConfigHandler->getParameter('chat_xmpp_host');
        $xmppPort = $this->platformConfigHandler->getParameter('chat_xmpp_port');

//        if (!empty($xmppHost) && !empty($xmppPort)) {
//            $connection = new XMPPHP_XMPP(
//                $xmppHost,
//                $xmppPort,
//                $authenticatedUser->getUsername(),
//                $authenticatedUser->getUsername(),
//                'xmpphp',
//                null,
//                false,
//                XMPPHP_Log::LEVEL_INFO
//            );
//
//            try {
//                $connection->connect();
//                $connection->processUntil('session_start');
//                $connection->presence();
//                $receiver = $user->getUsername() . '@' . $xmppHost;
//                $connection->message($receiver, 'Yahoo!');
//                $connection->disconnect();
//            } catch(XMPPHP_Exception $e) {
//                die($e->getMessage());
//            }
//        }
        return array(
            'authenticatedUser' => $authenticatedUser,
            'user' => $user,
            'xmppHost' => $xmppHost,
            'xmppPort' => $xmppPort
        );
    }


    /********************************
     * Plugin configuration methods *
     ********************************/


    /**
     * @EXT\Route(
     *     "/plugin/configure/form",
     *     name="claro_chat_plugin_configure_form"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function pluginConfigureFormAction()
    {
        $this->checkConfigurationAccess();

        $form = $this->formFactory->create(
            new PluginConfigurationType($this->platformConfigHandler)
        );

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/plugin/configure",
     *     name="claro_chat_plugin_configure"
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineChatBundle:Chat:pluginConfigureForm.html.twig")
     */
    public function pluginConfigureAction()
    {
        $this->checkConfigurationAccess();

        $formData = $this->request->get('chat_plugin_configuration_form');
        $host = $formData['host'];
        $port = $formData['port'];
        $this->platformConfigHandler->setParameters(
            array(
                'chat_xmpp_host' => $host,
                'chat_xmpp_port' => $port
            )
        );
        $form = $this->formFactory->create(
            new PluginConfigurationType($this->platformConfigHandler)
        );

        return array('form' => $form->createView());
    }

    private function checkConfigurationAccess()
    {
        $packagesTool = $this->toolManager->getAdminToolByName('platform_packages');

        if (is_null($packagesTool) ||
            !$this->authorization->isGranted('OPEN', $packagesTool)) {

            throw new AccessDeniedException();
        }
    }
}
