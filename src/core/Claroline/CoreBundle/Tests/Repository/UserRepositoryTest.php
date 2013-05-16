<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class UserRepositoryTest extends FixtureTestCase
{
    public function testFindAllExcept()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user', 'jane' => 'admin', 'bill' => 'user'));
        $repo = $this->em->getRepository('Claroline\CoreBundle\Entity\User');
        $users = $repo->findAllExcept($this->getUser('jane'));

        $this->assertEquals(2, count($users));
        $this->assertEquals('john', $users[0]->getFirstName());
        $this->assertEquals('bill', $users[1]->getFirstName());
    }
}