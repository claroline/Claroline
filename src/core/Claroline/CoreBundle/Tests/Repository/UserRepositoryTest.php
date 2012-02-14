<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class UserRepositoryTest extends FixtureTestCase
{
    /** @var UserRepository */
    private $userRepo;
    
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->userRepo = $this->getEntityManager()->getRepository('Claroline\CoreBundle\Entity\User');
    }
    
    public function testGetUsersByUsernameListReturnsExpectedResults()
    {
        $jane = $this->getFixtureReference('user/user');
        $bob = $this->getFixtureReference('user/user_2');
        $bill = $this->getFixtureReference('user/user_3');
        $henry = $this->getFixtureReference('user/ws_creator');
        
        $users = $this->userRepo->getUsersByUsernameList(
            array('user', 'user_2', 'user_3', 'ws_creator')
        );
        
        $this->assertEquals(4, count($users));
        $this->assertEquals($jane, $users[0]);
        $this->assertEquals($bob, $users[1]);
        $this->assertEquals($bill, $users[2]);
        $this->assertEquals($henry, $users[3]);
    }
}