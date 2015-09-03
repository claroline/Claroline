<?php

namespace UJM\ExoBundle\Tests\Controller\Api;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ExerciseControllerTest extends TransactionalTestCase
{
    public function test()
    {
        $this->markTestSkipped('Not implemented');

        $this->client->request('GET', '/exercise/api/test');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('TEST', $this->client->getResponse()->getContent());
    }
}
