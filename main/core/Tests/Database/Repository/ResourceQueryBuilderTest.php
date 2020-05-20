<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ResourceQueryBuilderTest extends MockeryTestCase
{
    /**
     * @expectedException \Claroline\CoreBundle\Repository\Exception\MissingSelectClauseException
     */
    public function testASelectClauseIsRequired()
    {
        $qb = new ResourceQueryBuilder();
        $qb->getDql();
    }

    public function testSelectAsEntity()
    {
        $qb = new ResourceQueryBuilder();

        $dql = $qb->selectAsEntity()->getDql();
        $eol = PHP_EOL;
        $expectedDql =
            "SELECT node{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}";
        $this->assertEquals($expectedDql, $dql);

        $dql = $qb->selectAsEntity(true)->getDql();
        $expectedDql =
            "SELECT node{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}";
        $this->assertEquals($expectedDql, $dql);
    }

    public function testFilters()
    {
        $eol = PHP_EOL;
        $qb = new ResourceQueryBuilder();

        $mockedWorkspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $mockedWorkspace->shouldReceive('getId')->once()->andReturn(123);
        $mockedParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $mockedParent->shouldReceive('getId')->once()->andReturn(456);

        $dql = $qb->selectAsEntity(true)
            ->whereInWorkspace($mockedWorkspace)
            ->whereParentIs($mockedParent)
            ->wherePathLike('foo', false)
            ->whereParentIsNull()
            ->groupById()
            ->getDql();

        $expectedDql =
            "SELECT node{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "WHERE node.workspace = :workspace_id{$eol}".
            "AND node.parent = :ar_parentId{$eol}".
            "AND node.path LIKE :pathlike{$eol}".
            "AND node.path <> :path{$eol}".
            "AND node.parent IS NULL{$eol}".
            "GROUP BY node.id{$eol}";

        $this->assertEquals($expectedDql, $dql);
        $this->assertEquals(
            [
                ':workspace_id' => 123,
                ':ar_parentId' => 456,
                ':pathlike' => 'foo%',
                ':path' => 'foo',
            ],
            $qb->getParameters()
        );
    }
}
