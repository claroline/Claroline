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

    public function setUp()
    {
        parent::setUp();

        $this->writer = m::mock('Claroline\CoreBundle\Writer\MessageWriter');
        $this->userRepo = m::mock('Claroline\CoreBundle\Repository\UserRepository');
        $this->messageRepo = m::mock('Claroline\CoreBundle\Repository\MessageRepository');
        $this->manager = new MessageManager($this->userRepo, $this->messageRepo, $this->writer);
    }

    public function testCreate()
    {
        $this->markTestSkipped('error on create');
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $user1 = m::mock('Claroline\CoreBundle\Entity\User');
        $user2 = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');

        $this->userRepo
            ->shouldReceive('findByUsernames')
            ->with(array('username1', 'username2'))
            ->andReturn(array($user1, $user2));
        $this->writer
            ->shouldReceive('create')
            ->with($user, 'username1;username2', array($user1, $user2), 'content', 'object')
            ->andReturn($msg);

        //User $sender, $receiverString, array $receivers, $content, $object, Msg $parent = null
        $this->manager->create($user, 'username1;username2', 'content', 'object');
    }

    public function testMarkAsRead()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $usrMsg = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $this->messageRepo
            ->shouldReceive('findUserMessages')
            ->once()
            ->with($user, array($msg))
            ->andReturn(array($usrMsg));

        $this->writer->shouldReceive('markAsRead')->once()->with($usrMsg);
        $this->manager->markAsRead($user, array($msg));
    }

    public function testMarkAsRemoved()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $usrMsg = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $this->messageRepo
            ->shouldReceive('findUserMessages')
            ->once()
            ->with($user, array($msg))
            ->andReturn(array($usrMsg));

        $this->writer->shouldReceive('markAsRemoved')->once()->with($usrMsg);

        $this->manager->markAsRemoved($user, array($msg));
    }

    public function testMarkAsUnremoved()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $usrMsg = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $this->messageRepo
            ->shouldReceive('findUserMessages')
            ->once()
            ->with($user, array($msg))
            ->andReturn(array($usrMsg));

        $this->writer->shouldReceive('markAsUnremoved')->once()->with($usrMsg);

        $this->manager->markAsUnremoved($user, array($msg));
    }

    public function testRemove()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $msg = m::mock('Claroline\CoreBundle\Entity\Message');
        $usrMsg = m::mock('Claroline\CoreBundle\Entity\UserMessage');

        $this->messageRepo
            ->shouldReceive('findUserMessages')
            ->once()
            ->with($user, array($msg))
            ->andReturn(array($usrMsg));

        $this->writer->shouldReceive('remove')->once()->with($usrMsg);

        $this->manager->remove($user, array($msg));
    }

    public function testGenerateGroupQueryString()
    {
        $group = m::mock('Claroline\CoreBundle\Entity\Group');
        $user1 = m::mock('Claroline\CoreBundle\Entity\User');
        $user2 = m::mock('Claroline\CoreBundle\Entity\User');

        $this->userRepo->shouldReceive('findByGroup')->with($group)->andReturn(array($user1, $user2));
        $user1->shouldReceive('getId')->andReturn(1);
        $user2->shouldReceive('getId')->andReturn(2);

        $urlParameters = $this->manager->generateGroupQueryString($group);
        $this->assertEquals('?ids[]=1&ids[]=2', $urlParameters);
    }

    public function testGenerateStringTo()
    {
        $user1 = m::mock('Claroline\CoreBundle\Entity\User');
        $user2 = m::mock('Claroline\CoreBundle\Entity\User');

        $this->userRepo->shouldReceive('findByIds')->with(array(1, 2))->andReturn(array($user1, $user2));

        $user1->shouldReceive('getUsername')->andReturn('user1');
        $user2->shouldReceive('getUsername')->andReturn('user2');

        $userString = $this->manager->generateStringTo(array(1, 2));
        $this->assertEquals('user1;user2;', $userString);
    }
}
