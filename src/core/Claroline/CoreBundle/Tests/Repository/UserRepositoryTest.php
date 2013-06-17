<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class UserRepositoryTest extends FixtureTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('John' => 'user', 'Jane' => 'admin', 'Bill' => 'user'));
        $this->repo = $this->em->getRepository('Claroline\CoreBundle\Entity\User');
    }

    public function testFindAllExcept()
    {
        $users = $this->repo->findAllExcept($this->getUser('Jane'));
        $this->assertEquals(2, count($users));
        $this->assertEquals('John', $users[0]->getFirstName());
        $this->assertEquals('Bill', $users[1]->getFirstName());
    }

    public function testFindByName()
    {
        $users = $this->repo->findByName('n');
        $this->assertEquals(2, count($users));
        $this->assertEquals('John', $users[0]->getFirstName());
        $this->assertEquals('Jane', $users[1]->getFirstName());

        $users = $this->repo->findByName('Jane Doe');
        $this->assertEquals(1, count($users));
    }
}