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

use Claroline\ChatBundle\Manager\ChatManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

require_once __DIR__ . "/../Library/xmpphp-master/XMPPHP/XMPP.php";
require_once __DIR__ . "/../Library/xmpphp-master/XMPPHP/Log.php";
require_once __DIR__ . "/../Library/xmpphp-master/XMPPHP/Exception.php";
use \XMPPHP_XMPP;
use \XMPPHP_Log;
use \XMPPHP_Exception;

class ChatController extends Controller
{
    private $chatManager;
    private $platformConfigHandler;

    /**
     * @DI\InjectParams({
     *     "chatManager"           = @DI\Inject("claroline.manager.chat_manager"),
     *     "platformConfigHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        ChatManager $chatManager,
        PlatformConfigurationHandler $platformConfigHandler
    )
    {
        $this->chatManager = $chatManager;
        $this->platformConfigHandler = $platformConfigHandler;
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
}
