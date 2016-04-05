<?php

namespace HeVinci\CompetencyBundle\Repository;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class CompetencyRepositoryTest extends RepositoryTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->repo = $this->om->getRepository('HeVinciCompetencyBundle:Competency');
    }

    public function testFindRootsByName()
    {
        $r1 = $this->persistCompetency('FOO');
        $r2 = $this->persistCompetency('BAR');
        $this->persistCompetency('BAZ', $r1);
        $this->persistCompetency('FOO', $r2);
        $this->om->flush();

        $this->assertEquals([$r1], $this->repo->findRootsByName('FOO'));
    }

    public function testFindForProgressComputingReturnsEmptyArrayIfStartNodeHasNoParent()
    {
        $c = $this->persistCompetency('C');
        $competencies = $this->repo->findForProgressComputing($c);
        $this->assertEquals(0, count($competencies));
    }

    public function testFindForProgressComputing()
    {
        /*
         *  C1
         *      C2
         *          C4
         *              C8
         *              C9
         *          C5
         *      C3
         *          C6
         *          C7
         */

        $c1 = $this->persistCompetency('C1');
        $c2 = $this->persistCompetency('C2', $c1);
        $c3 = $this->persistCompetency('C3', $c1);
        $c4 = $this->persistCompetency('C4', $c2);
        $c5 = $this->persistCompetency('C5', $c2);
        $this->persistCompetency('C6', $c3);
        $this->persistCompetency('C7', $c3);
        $this->persistCompetency('C8', $c4);
        $this->persistCompetency('C9', $c4);

        $this->om->flush();

        $competencies = $this->repo->findForProgressComputing($c4);
        $this->assertEquals(4, count($competencies));
        $this->assertContains($c1, $competencies);
        $this->assertContains($c2, $competencies);
        $this->assertContains($c3, $competencies);
        $this->assertContains($c5, $competencies);
    }
}
