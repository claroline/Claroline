<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ChatBundle\Repository;

use Claroline\ChatBundle\Entity\ChatRoom;
use Doctrine\ORM\EntityRepository;

class ChatRoomMessageRepository extends EntityRepository
{
    public function findMessagesByChatRoom(ChatRoom $chatRoom)
    {
        $dql = '
            SELECT crm
            FROM Claroline\ChatBundle\Entity\ChatRoomMessage crm
            WHERE crm.chatRoom = :chatRoom
            ORDER BY crm.creationDate ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('chatRoom', $chatRoom);

        return $query->getResult();
    }

    public function findChatRoomParticipantsName(ChatRoom $chatRoom)
    {
        $dql = '
            SELECT DISTINCT crm.userFullName
            FROM Claroline\ChatBundle\Entity\ChatRoomMessage crm
            WHERE crm.chatRoom = :chatRoom
            ORDER BY crm.userFullName ASC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('chatRoom', $chatRoom);

        return $query->getResult();
    }
}
