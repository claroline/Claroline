<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ResultBundle\Manager;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ResultBundle\Entity\Result;
use Claroline\ResultBundle\Testing\Persister;

class ResultManagerTest extends TransactionalTestCase
{
    /** @var ResultManager */
    private $manager;
    /** @var ObjectManager */
    private $om;
    /** @var Persister */
    private $persist;

    protected function setUp()
    {
        parent::setUp();
        $container = $this->client->getContainer();
        $this->manager = $container->get('claroline.result.result_manager');
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->persist = new Persister($this->om);
    }

    public function testCreateAndDelete()
    {
        $repo = $this->om->getRepository('ClarolineResultBundle:Result');
        $result = $this->manager->create(new Result());
        $results = $repo->findAll();
        $this->assertEquals(1, count($results));
        $this->assertEquals($result, $results[0]);
        $this->manager->delete($results[0]);
        $this->assertEquals(0, count($repo->findAll()));
    }

    public function testWidget()
    {
        $bob = $this->persist->user('bob');
        $this->om->flush();
        $content = $this->manager->getWidgetContent($bob->getPersonalWorkspace(), $bob);
        $this->assertNotEmpty($content);
    }

    public function testGetMarksWithAndWithoutFullAccess()
    {
        $bob = $this->persist->user('bob');
        $bill = $this->persist->user('bill');
        $jane = $this->persist->user('jane');

        $result = $this->persist->result('eval 1', $bob);

        $billMark = $this->persist->mark($result, $bill, 12);
        $janeMark = $this->persist->mark($result, $jane, 14);

        $this->om->flush();

        $expected = [
            [
                'id' => $bill->getId(),
                'name' => 'bill bill',
                'mark' => 12,
                'markId' => $billMark->getId()
            ],
            [
                'id' => $jane->getId(),
                'name' => 'jane jane',
                'mark' => 14,
                'markId' => $janeMark->getId()
            ]
        ];
        $actual = $this->manager->getMarks($result, $bob, true);
        $this->assertEquals($expected, $actual);
    }
}
