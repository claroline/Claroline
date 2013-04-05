<?php

namespace Claroline\CoreBundle\Library\User;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\User;

class CreatorTest extends FunctionalTestCase
{
    /** @var Creator */
    private $creator;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->creator = $this->client->getContainer()->get('claroline.user.creator');
    }

    public function testCreateUserGivesUserRole()
    {
        $user = new User();
        $user->setFirstName('123');
        $user->setLastName('123');
        $user->setPassword('123');
        $user->setUsername('123');
        $user = $this->creator->create($user);
        $this->assertTrue($user->hasRole('ROLE_USER'));
    }
}