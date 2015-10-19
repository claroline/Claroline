<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Manager;

use Claroline\ChatBundle\Entity\ChatUser;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.chat_manager")
 */
class ChatManager
{
    private $om;
    private $chatUserRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->chatUserRepo = $om->getRepository('ClarolineChatBundle:ChatUser');
    }

    public function persistChatUser(ChatUser $chatUser)
    {
        $this->om->persist($chatUser);
        $this->om->flush();
    }

    public function deleteChatUser(ChatUser $chatUser)
    {
        $this->om->remove($chatUser);
    }


    /****************************************
     * Access to ChatUserRepository methods *
     ****************************************/

    public function getChatUserByUser(User $user)
    {
        return $this->chatUserRepo->findChatUserByUser($user);
    }
}
