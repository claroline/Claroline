<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Controller;

use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ResultBundle\Testing\Persister;

class ResultControllerTest extends TransactionalTestCase
{
    use RequestTrait;

    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
    }

    public function testOpenNonExistentResult()
    {
        $john = $this->persist->user('john');
        $this->om->flush();

        $this->request('GET', '/results/1', $john);
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testOpenNotAllowedResult()
    {
        $john = $this->persist->user('john');
        $jane = $this->persist->user('jane');
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('GET', "/results/{$result->getId()}", $jane);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testOpen()
    {
        $john = $this->persist->user('john');
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('GET', "/results/{$result->getId()}", $john);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains($result->getResourceNode()->getName(), $this->client->getResponse()->getContent());
    }
}
