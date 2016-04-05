<?php

namespace HeVinci\UrlBundle\Manager;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class UrlManagerTest extends TransactionalTestCase
{
    public function testSetUrl()
    {
        $this->client->getContainer()->get('hevinci_url.manager.url');
        $this->markTestIncomplete('Not implemented yet');
    }
}
