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
}
