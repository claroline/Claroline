<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


 namespace Claroline\MessageBundle\Controller;

 use FOS\RestBundle\Controller\FOSRestController;
 use FOS\RestBundle\Controller\Annotations\View;
 use JMS\DiExtraBundle\Annotation as DI;
 use Nelmio\ApiDocBundle\Annotation\ApiDoc;
 use Claroline\MessageBundle\Manager\MessageManager;
 use Claroline\MessageBundle\Entity\Message;
 use Claroline\CoreBundle\Manager\UserManager;
 use Claroline\CoreBundle\Entity\User;
 use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

 class ApiMessageController extends FOSRestController {

   private $messageManager;
   private $tokenStorage;
   private $userManager;

   /**
    * @DI\InjectParams({
    *     "messageManager" = @DI\Inject("claroline.manager.message_manager"),
    *     "tokenStorage"        = @DI\Inject("security.token_storage"),
    *     "userManager" = @DI\Inject("claroline.manager.user_manager")
    * })
    */

   public function _construct(MessageManager $messageManager, $tokenStorage, UserManager $userManager)
   {
       $this->messageManager = $messageManager;
       $this->tokenStorage = $tokenStorage;
       $this->userManager = $userManager;
   }

   /**
    * @Route("/received.{_format}", name="claro_received_message", defaults={"_format":"json"})
    * @View(serializerGroups={"api_message"})
    */
   public function getReceivedAction()
   {
       $user = $this->tokenStorage->getToken()->getUser();

       return $this->messageManager->getReceivedMessagesJson($user);
   }

   /**
    * @Route("/sent.{_format}", name="claro_sent_message", defaults={"_format":"json"})
    * @View(serializerGroups={"api_message"})
    */
    public function getSentAction()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->messageManager->getSentMessagesJson($user);
    }

    /**
     * @Route("/removed.{_format}", name="claro_removed_message", defaults={"_format":"json"})
     * @View(serializerGroups={"api_message"})
     */
    public function getRemovedAction()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return $this->messageManager->getRemovedMessagesJson($user);
    }

 }
