<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\UserMessage;

class MessageManagerTest extends MockeryTestCase
{
    private $om;
    private $pagerFactory;
    private $userRepo;
    private $messageRepo;
    private $userMessageRepo;
    private $manager;

    public function setUp()
    {
        parent::setUp();
        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->pagerFactory = m::mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->messageRepo = m::mock('Claroline\CoreBundle\Repository\MessageRepository');
        $this->userMessageRepo = m::mock('Claroline\CoreBundle\Repository\UserMessageRepository');
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:User')
            ->andReturn($this->userRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Message')
            ->andReturn($this->messageRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:UserMessage')
            ->andReturn($this->userMessageRepo);
        $this->manager = new MessageManager($this->om, $this->pagerFactory);
    }

    public function testSend()
    {
        $sender = new User();
        $receiver1 = new User();
        $receiver2 = new User();
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $msgParent = m::mock('Claroline\CoreBundle\Entity\Message');
        $userMessage1 = m::mock('Claroline\CoreBundle\Entity\UserMessage');
        $userMessage2 = m::mock('Claroline\CoreBundle\Entity\UserMessage');
        $userMessage3 = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $msg->shouldReceive('getTo')->once()->andReturn('user1;user2');
        $this->userRepo->shouldReceive('findByUsernames')
            ->once()
            ->with(array('user1','user2'))
            ->andReturn(array($receiver1, $receiver2));
        $msg->shouldReceive('setSender')->once()->with($sender);
        $msg->shouldReceive('setParent')->once()->with($msgParent);
        $this->om->shouldReceive('persist')->once()->with($msg);
        $this->om->shouldReceive('factory')
            ->times(3)
            ->with('Claroline\CoreBundle\Entity\UserMessage')
            ->andReturn($userMessage1, $userMessage2, $userMessage3);
        $userMessage1->shouldReceive('setIsSent')->once()->with(true);
        $userMessage1->shouldReceive('setUser')->once()->with($sender);
        $userMessage1->shouldReceive('setMessage')->once()->with($msg);
        $userMessage2->shouldReceive('setUser')->once()->with($receiver1);
        $userMessage2->shouldReceive('setMessage')->once()->with($msg);
        $userMessage3->shouldReceive('setUser')->once()->with($receiver2);
        $userMessage3->shouldReceive('setMessage')->once()->with($msg);
        $this->om->shouldReceive('persist')->once()->with($userMessage1);
        $this->om->shouldReceive('persist')->once()->with($userMessage2);
        $this->om->shouldReceive('persist')->once()->with($userMessage3);
        $this->om->shouldReceive('flush')->once();

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
        $usrMsg1->shouldReceive('markAs' . $flag)->once();
        $usrMsg2->shouldReceive('markAs' . $flag)->once();
        $this->om->shouldReceive('persist')->with($usrMsg1)->once();
        $this->om->shouldReceive('persist')->with($usrMsg2)->once();
        $this->om->shouldReceive('flush')->once();
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

    public function testGetConversation()
    {
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');

        $this->messageRepo->shouldReceive('findAncestors')->with($msg)->andReturn($msg);
        $this->assertEquals($msg, $this->manager->getConversation($msg));
    }

    public function testRemove()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $usrMsg = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $this->userMessageRepo->shouldReceive('findByMessages')->once()->andReturn(array($usrMsg));
        $this->om->shouldReceive('remove')->with($usrMsg)->once();
        $this->om->shouldReceive('flush')->once();
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
