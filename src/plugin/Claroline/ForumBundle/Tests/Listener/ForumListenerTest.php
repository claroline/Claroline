<?php

namespace Claroline\ForumBundle\Listener;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ForumListenerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testForumCreation()
    {
         $this->logUser($this->getFixtureReference('user/user'));
//         $this->client->request('GET', 'resource/')
    }

}
