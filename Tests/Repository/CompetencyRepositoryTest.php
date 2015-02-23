<?php

namespace HeVinci\CompetencyBundle\Repository;

use Claroline\CoreBundle\Library\Testing\TransactionalTestCase;
use HeVinci\CompetencyBundle\Entity\Competency;

class CompetencyRepositoryTest extends TransactionalTestCase
{
    public function testFindRootsByName()
    {
        $container = $this->client->getContainer();
        $om = $container->get('claroline.persistence.object_manager');
        $repo = $om->getRepository('HeVinciCompetencyBundle:Competency');

        $firstRoot = new Competency();
        $secondRoot = new Competency();
        $child = new Competency();

        $firstRoot->setName('FOO');
        $firstRoot->setDescription('ROOT DESC');
        $secondRoot->setName('BAR');
        $child->setName('FOO');
        $child->setParent($secondRoot);

        $om->persist($firstRoot);
        $om->persist($secondRoot);
        $om->persist($child);
        $om->flush();

        $roots = $repo->findRootsByName('FOO');
        $this->assertEquals(1, count($roots));
        $this->assertEquals('ROOT DESC', $roots[0]->getDescription());
    }
}
