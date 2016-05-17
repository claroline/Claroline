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
   public function _construct(MessageManager $messageManager, $tokenStorage, UserManager $userManager){
     $this->messageManager = $messageManager;
     $this->tokenStorage = $tokenStorage;
     $this->userManager = $userManager;
   }

   /**
    * @View(serializerGroups={"api_message"})
    */
   public function getReceivedMessagesAction(){
      $user = $this->tokenStorage->getToken()->getUser();
      return $this->messageManager->getReceivedMessagesJson($user);
   }

    /**
    * @View(serializerGroups={"api_message"})
    */
    public function getSentMessagesAction(){
      $user = $this->tokenStorage->getToken()->getUser();
      return $this->messageManager->getSentMessagesJson($user);
    }

    /**
    * @View(serializerGroups={"api_message"})
    */
    public function getRemovedMessagesAction(){
      $user = $this->tokenStorage->getToken()->getUser();
       return $this->messageManager->getRemovedMessagesJson($user);
    }

    // public function postSendMessageAction($content, $object, array $usernames){
    //   $sender = $this->tokenStorage->getToken()->getUser();
    // }








 }
