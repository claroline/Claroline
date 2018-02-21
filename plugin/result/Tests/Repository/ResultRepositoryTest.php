<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Repository;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\ResultBundle\Testing\Persister;

class ResultRepositoryTest extends TransactionalTestCase
{
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

    public function testFindByUserAndWorkspace()
    {
        /** @var ResultRepository $repo */
        $repo = $this->om->getRepository('ClarolineResultBundle:Result');

        $john = $this->persist->user('john');
        $jane = $this->persist->user('jane');

        $res1 = $this->persist->result('Res 1', $john);
        $res2 = $this->persist->result('Res 2', $john);
        $res3 = $this->persist->result('Res 3', $jane);

        $mark1 = $this->persist->mark($res1, $jane, 12);
        $mark2 = $this->persist->mark($res2, $jane, 15);
        $mark3 = $this->persist->mark($res2, $john, 19);
        $mark4 = $this->persist->mark($res3, $john, 8);

        $this->om->flush();

        $janeResults = $repo->findByUserAndWorkspace($jane, $john->getPersonalWorkspace());

        $this->assertEquals(2, count($janeResults));
        $this->assertEquals($res1->getResourceNode()->getName(), $janeResults[0]['title']);
        $this->assertEquals($mark1->getValue(), $janeResults[0]['mark']);
        $this->assertEquals($res2->getResourceNode()->getName(), $janeResults[1]['title']);
        $this->assertEquals($mark2->getValue(), $janeResults[1]['mark']);

        $johnResults1 = $repo->findByUserAndWorkspace($john, $john->getPersonalWorkspace());

        $this->assertEquals(1, count($johnResults1));
        $this->assertEquals($res2->getResourceNode()->getName(), $johnResults1[0]['title']);
        $this->assertEquals($mark3->getValue(), $johnResults1[0]['mark']);

        $johnResults2 = $repo->findByUserAndWorkspace($john, $jane->getPersonalWorkspace());

        $this->assertEquals(1, count($johnResults2));
        $this->assertEquals($res3->getResourceNode()->getName(), $johnResults2[0]['title']);
        $this->assertEquals($mark4->getValue(), $johnResults2[0]['mark']);
    }
}
