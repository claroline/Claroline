<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Controller\API;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\View;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\MessageBundle\Manager\MessageManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Claroline\CoreBundle\Entity\User;

/**
 * This class still needs to be able to handle pagination.
 */
class MessageController extends FOSRestController
{
    private $messageManager;

   /**
    * @DI\InjectParams({
    *     "messageManager" = @DI\Inject("claroline.manager.message_manager")
    * })
    */
   public function _construct(MessageManager $messageManager)
   {
       $this->messageManager = $messageManager;
   }

   /**
    * @Route("/received.{_format}", name="claro_received_message", defaults={"_format":"json"})
    * @View(serializerGroups={"api_message"})
    * @EXT\ParamConverter("user", converter="current_user")
    */
   public function getReceivedAction(User $user)
   {
       return $this->messageManager->getReceivedMessages($user);
   }

    /**
     * @Route("/sent.{_format}", name="claro_sent_message", defaults={"_format":"json"})
     * @View(serializerGroups={"api_message"})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function getSentAction(User $user)
    {
        return $this->messageManager->getSentMessages($user);
    }

    /**
     * @Route("/removed.{_format}", name="claro_removed_message", defaults={"_format":"json"})
     * @View(serializerGroups={"api_message"})
     * @EXT\ParamConverter("user", converter="current_user")
     */
    public function getRemovedAction(User $user)
    {
        return $this->messageManager->getRemovedMessages($user);
    }
}
