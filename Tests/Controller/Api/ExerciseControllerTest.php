<?php

namespace UJM\ExoBundle\Tests\Controller\Api;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;

class ExerciseControllerTest extends TransactionalTestCase
{
    public function testExportActionIsReachable()
    {
        $this->client->request('GET', '/exercise/api/exercises/1');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
        $this->assertContains(
            'UJM\ExoBundle\Entity\Exercise object not found',
            $this->client->getResponse()->getContent()
        );
    }
}
