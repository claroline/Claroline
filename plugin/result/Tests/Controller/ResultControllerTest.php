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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\RequestTrait;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
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
        $this->persist = new Persister($this->om, $this->client->getContainer());
    }

    public function testOpenNonExistentResult()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john');
        $this->om->flush();

        $this->request('GET', '/results/1', $john);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testOpenNotAllowedResult()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $jane = $this->persist->user('jane');
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('GET', "/results/{$result->getId()}", $jane);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testOpen()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('GET', "/results/{$result->getId()}", $john);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertContains($result->getResourceNode()->getName(), $this->client->getResponse()->getContent());
    }

    public function testCreateMarkNotAllowed()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $jane = $this->persist->user('jane');
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('POST', "/results/{$result->getId()}/users/{$john->getId()}", $jane, [
            'mark' => 12,
        ]);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateMarkNoMark()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('POST', "/results/{$result->getId()}/users/{$john->getId()}", $john);
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateMarkInvalidMark()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('POST', "/results/{$result->getId()}/users/{$john->getId()}", $john, [
            'mark' => 123,
        ]);
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateMark()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $this->om->flush();

        $this->request('POST', "/results/{$result->getId()}/users/{$john->getId()}", $john, [
            'mark' => 12,
        ]);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $marks = $this->om->getRepository('ClarolineResultBundle:Mark')->findAll();
        $this->assertEquals(1, count($marks));
        $this->assertEquals($john, $marks[0]->getUser());
        $this->assertEquals($result, $marks[0]->getResult());
        $this->assertEquals(12, $marks[0]->getValue());
    }

    public function testDeleteMarkNotAllowed()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $jane = $this->persist->user('jane');
        $result = $this->persist->result('Result 1', $john);
        $mark = $this->persist->mark($result, $jane, 16);
        $this->om->flush();

        $this->request('DELETE', "/results/marks/{$mark->getId()}", $jane);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteMark()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $mark = $this->persist->mark($result, $john, 16);
        $this->om->flush();

        $this->request('DELETE', "/results/marks/{$mark->getId()}", $john);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, count($this->om->getRepository('ClarolineResultBundle:Mark')->findAll()));
    }

    public function testEditMarkNotAllowed()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $jane = $this->persist->user('jane');
        $result = $this->persist->result('Result 1', $john);
        $mark = $this->persist->mark($result, $jane, 16);
        $this->om->flush();

        $this->request('PUT', "/results/marks/{$mark->getId()}", $jane, [
            'value' => 19,
        ]);
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(16, $mark->getValue());
    }

    public function testEditMarkNoValue()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $mark = $this->persist->mark($result, $john, 11);
        $this->om->flush();

        $this->request('PUT', "/results/marks/{$mark->getId()}", $john);
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(11, $mark->getValue());
    }

    public function testEditMark()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $mark = $this->persist->mark($result, $john, 14);
        $this->om->flush();

        $this->request('PUT', "/results/marks/{$mark->getId()}", $john, [
            'value' => 18,
        ]);
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(18, $mark->getValue());
    }

    public function testEditMarkInvalidMark()
    {
        $this->markTestSkipped('Temporarily deactivated.');
        $john = $this->persist->user('john', true);
        $result = $this->persist->result('Result 1', $john);
        $mark = $this->persist->mark($result, $john, 14);
        $this->om->flush();

        $this->request('PUT', "/results/marks/{$mark->getId()}", $john, [
            'mark' => 123,
        ]);
        $this->assertEquals(422, $this->client->getResponse()->getStatusCode());
    }
}
