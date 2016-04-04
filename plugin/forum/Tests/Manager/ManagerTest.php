<?php

namespace Claroline\ForumBundle\Manager;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ManagerTest extends TransactionalTestCase
{
    public function testSomething()
    {
        $this->client->getContainer()->get('claroline.manager.forum_manager');
        $this->markTestIncomplete('Not implemented yet');
    }
}
