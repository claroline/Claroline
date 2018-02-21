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

class MarkRepositoryTest extends TransactionalTestCase
{
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;
    /** @var MarkRepository */
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->om = $this->client->getContainer()->get('claroline.persistence.object_manager');
        $this->repo = $this->om->getRepository('ClarolineResultBundle:Mark');
        $this->persist = new Persister($this->om, $this->client->getContainer());
    }

    public function testFindByResult()
    {
        $bob = $this->persist->user('bob');
        $bill = $this->persist->user('bill');
        $jane = $this->persist->user('jane');

        $result1 = $this->persist->result('eval 1', $bob);
        $result2 = $this->persist->result('eval 2', $bob);

        $mark1 = $this->persist->mark($result1, $bill, '12');
        $mark2 = $this->persist->mark($result1, $jane, '14');
        $this->persist->mark($result2, $jane, '15');

        $this->om->flush();

        $expected = [
            [
                'id' => $bill->getId(),
                'name' => 'bill bill',
                'mark' => '12',
                'markId' => $mark1->getId(),
            ],
            [
                'id' => $jane->getId(),
                'name' => 'jane jane',
                'mark' => '14',
                'markId' => $mark2->getId(),
            ],
        ];
        $actual = $this->repo->findByResult($result1);
        $this->assertEquals($expected, $actual);
    }

    public function testFindByResultAndUser()
    {
        $bob = $this->persist->user('bob');
        $bill = $this->persist->user('bill');
        $jane = $this->persist->user('jane');

        $result1 = $this->persist->result('eval 1', $bob);
        $result2 = $this->persist->result('eval 2', $bob);

        $this->persist->mark($result1, $bill, '12');
        $this->persist->mark($result2, $jane, '15');
        $mark = $this->persist->mark($result1, $jane, '14');

        $this->om->flush();

        $expected = [
            [
                'id' => $jane->getId(),
                'name' => 'jane jane',
                'mark' => '14',
                'markId' => $mark->getId(),
            ],
        ];
        $actual = $this->repo->findByResultAndUser($result1, $jane);
        $this->assertEquals($expected, $actual);
    }
}
