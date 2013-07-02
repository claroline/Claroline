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
        $this->creator = $this->client->getContainer()->get('claroline.manager.user_manager');
    }

    public function testCreateUserGivesUserRole()
    {
        $newUser = new User();
        $newUser->setFirstName('123');
        $newUser->setLastName('123');
        $newUser->setPassword('123');
        $newUser->setUsername('123');
        $user = $this->creator->createUser($newUser);
        $this->assertTrue($user->hasRole('ROLE_USER'));
    }
}