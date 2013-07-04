<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class MessageManagerTest extends MockeryTestCase
{
    /** @var MessageManager */
    private $manager;
    private $writer;
    private $userRepo;
    private $messageRepo;
    private $userMessageRepo;
    private $pagerFactory;

    public function setUp()
    {
        parent::setUp();

        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->messageRepo = m::mock('Claroline\CoreBundle\Repository\MessageRepository');
        $this->userMessageRepo = m::mock('Claroline\CoreBundle\Repository\UserMessageRepository');
        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->pagerFactory = m::mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->manager = new MessageManager(
            $this->userMessageRepo,
            $this->messageRepo,
            $this->userRepo,
            $this->writer,
            $this->pagerFactory
        );
    }
    public function testSend()
    {
        $sender = m::mock('Claroline\CoreBundle\Entity\User');
        $receiver1 = m::mock('Claroline\CoreBundle\Entity\User');
        $receiver2 = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $msgParent = m::mock('Claroline\CoreBundle\Entity\Message');

        $msg->shouldReceive('getTo')->once()->andReturn('user1;user2');
        $this->userRepo->shouldReceive('findByUsernames')
            ->once()
            ->with(array('user1','user2'))
            ->andReturn(array($receiver1, $receiver2));
        $msg->shouldReceive('setSender')->once()->with($sender);

        $msg->shouldReceive('setParent')->once()->with($msgParent);
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->writer->shouldReceive('create')->with($msg)->once();
        $this->writer->shouldReceive('create')->with(anInstanceOf('Claroline\CoreBundle\Entity\UserMessage'))->atLeast()->twice();
        $this->writer->shouldReceive('forceFlush')->once();

        $this->manager->send($sender, $msg, $msgParent);

    }

    /**
     * @dataProvider getMessagesProvider
     */
    public function testGetMessages($repoMethod, $managerMethod)
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $query = new \Doctrine\ORM\Query(m::mock('Doctrine\ORM\EntityManager'));
        $this->userMessageRepo->shouldReceive($repoMethod)->once()->with($user, '')->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')->once()->with($query, 1)->andReturn('pager');
        $this->assertEquals('pager', $this->manager->{$managerMethod}($user, '', 1));
    }

    /**
     * @dataProvider findMessagesProvider
     */
    public function testFindMessages($repoMethod, $search, $managerMethod)
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $query = new \Doctrine\ORM\Query(m::mock('Doctrine\ORM\EntityManager'));
        $this->userMessageRepo->shouldReceive($repoMethod)->once()->with($user, $search, false)->andReturn($query);
        $this->pagerFactory->shouldReceive('createPager')->once()->with($query, 1)->andReturn('pager');
        $this->assertEquals('pager', $this->manager->{$managerMethod}($user, $search, 1));
    }

    public function getMessagesProvider()
    {
        return array(
            array('findReceived', 'getReceivedMessages'),
            array('findSent', 'getSentMessages'),
            array('findRemoved', 'getRemovedMessages')
        );
    }

    public function findMessagesProvider()
    {
        return array(
            array('findReceivedByObjectOrSender', 'foo', 'getReceivedMessages'),
            array('findSentByObject', 'foo', 'getSentMessages'),
            array('findRemovedByObjectOrSender', 'foo', 'getRemovedMessages')
        );
    }

    /**
     * @dataProvider testMarkAsReadProvider
     */
    public function testSetMarkAsRead($flag, $managerMethod)
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $usrMsg1 = m::mock('Claroline\CoreBundle\Entity\UserMessage');
        $usrMsg2 = m::mock('Claroline\CoreBundle\Entity\UserMessage');
        $this->userMessageRepo->shouldReceive('findByMessages')
            ->once()
            ->with($user, array('message1', 'message2'))
            ->andReturn(array($usrMsg1, $usrMsg2));
        $this->writer->shouldReceive('suspendFlush')->once();
        $usrMsg1->shouldReceive('markAs' . $flag)->once();
        $usrMsg2->shouldReceive('markAs' . $flag)->once();
        $this->writer->shouldReceive('update')->with($usrMsg1)->once();
        $this->writer->shouldReceive('update')->with($usrMsg2)->once();
        $this->writer->shouldReceive('forceFlush')->once();
        $this->manager->{$managerMethod}($user, array('message1', 'message2'));
    }

    public function testMarkAsReadProvider()
    {
        return array(
            array('Read','markAsRead'),
            array('Removed','markAsRemoved'),
            array('Unremoved','markAsUnremoved')
            );
    }

    public function testgetConversation()
    {
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');

        $this->messageRepo->shouldReceive('findAncestors')->with($msg);
        $this->manager->getConversation($msg);
    }

    public function testRemove()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $usrMsg = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $this->userMessageRepo->shouldReceive('findByMessages')->once()->andReturn(array($usrMsg));
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->writer->shouldReceive('delete')->with($usrMsg)->once();
        $this->writer->shouldReceive('forceFlush')->once();
        $this->manager->remove($user, array($msg));
    }

    public function testgenerateGroupeQrStr()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $users = array();

        for ($i = 0; $i < 3; $i++) {

            $user = m::mock('Claroline\CoreBundle\Entity\User');
            $user->shouldReceive('getId')->once()->andReturn($i);
            $users[] = $user;
            }

        $this->userRepo->shouldReceive('findByGroup')->once()->with($group)->andReturn($users);
        $this->assertEquals('?ids[]=0&ids[]=1&ids[]=2', $this->manager->generateGroupQueryString($group));
     }


}
