<?php

namespace HeVinci\CompetencyBundle\Repository;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class CompetencyProgressRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->om->getRepository('HeVinciCompetencyBundle:Progress\CompetencyProgress');
    }

    public function testFindByUserAndCompetencyIds()
    {
        $u1 = $this->persistUser('u1');
        $u2 = $this->persistUser('u2');
        $c1 = $this->persistCompetency('c1');
        $c2 = $this->persistCompetency('c2');
        $c3 = $this->persistCompetency('c3');
        $p1 = $this->persistCompetencyProgress($u1, $c1);
        $p2 = $this->persistCompetencyProgress($u1, $c2); // extra data
        $p3 = $this->persistCompetencyProgress($u2, $c2);
        $p4 = $this->persistCompetencyProgress($u2, $c3);

        $this->om->flush();

        $this->assertEquals(0, count($this->repo->findByUserAndCompetencyIds($u1, [])));
        $this->assertEquals(1, count($result = $this->repo->findByUserAndCompetencyIds($u1, [$c1->getId()])));
        $this->assertContains($p1, $result);
        $this->assertEquals(2, count($result = $this->repo->findByUserAndCompetencyIds($u2, [$c2->getId(), $c3->getId()])));
        $this->assertContains($p3, $result);
        $this->assertContains($p4, $result);
    }
}
