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
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.icon icon{$eol}";
        $this->assertEquals($expectedDql, $dql);
    }

    public function testSelectAsArray()
    {
        $qb = new ResourceQueryBuilder();

        $dql = $qb->selectAsArray()->getDql();
        $eol = PHP_EOL;
        $expectedDql =
            "SELECT DISTINCT{$eol}".
            "    node.id as id,{$eol}".
            "    node.uuid as uuid,{$eol}".
            "    node.name as name,{$eol}".
            "    node.path as path,{$eol}".
            "    IDENTITY(node.parent) as parent_id,{$eol}".
            "    creator.username as creator_username,{$eol}".
            "    creator.id as creator_id,{$eol}".
            "    resourceType.name as type,{$eol}".
            "    icon.relativeUrl as large_icon,{$eol}".
            "    node.mimeType as mime_type,{$eol}".
            "    node.index as index_dir,{$eol}".
            "    node.creationDate as creation_date,{$eol}".
            "    node.modificationDate as modification_date,{$eol}".
            "    node.published as published,{$eol}".
            "    node.accessibleFrom as accessible_from,{$eol}".
            "    node.accessibleUntil as accessible_until,{$eol}".
            "    node.deletable as deletable{$eol}".
            "{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.icon icon{$eol}";

        $this->assertEquals($expectedDql, $dql);

        $dql = $qb->selectAsArray(true)->getDql();
        $expectedDql =
            "SELECT DISTINCT{$eol}".
            "    node.id as id,{$eol}".
            "    node.uuid as uuid,{$eol}".
            "    node.name as name,{$eol}".
            "    node.path as path,{$eol}".
            "    IDENTITY(node.parent) as parent_id,{$eol}".
            "    creator.username as creator_username,{$eol}".
            "    creator.id as creator_id,{$eol}".
            "    resourceType.name as type,{$eol}".
            "    icon.relativeUrl as large_icon,{$eol}".
            "    node.mimeType as mime_type,{$eol}".
            "    node.index as index_dir,{$eol}".
            "    node.creationDate as creation_date,{$eol}".
            "    node.modificationDate as modification_date,{$eol}".
            "    node.published as published,{$eol}".
            "    node.accessibleFrom as accessible_from,{$eol}".
            "    node.accessibleUntil as accessible_until,{$eol}".
            "    node.deletable as deletable{$eol}".
            ",{$eol}rights.mask{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.icon icon{$eol}".
            "LEFT JOIN node.rights rights{$eol}".
            "JOIN rights.role rightRole{$eol}";
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
            ->whereCanOpen()
            ->whereTypeIn(['baz', 'bat'])
            ->whereParentIsNull()
            ->groupById()
            ->getDql();

        $expectedDql =
            "SELECT node{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.icon icon{$eol}".
            "LEFT JOIN node.rights rights{$eol}".
            "JOIN rights.role rightRole{$eol}".
            "WHERE node.workspace = :workspace_id{$eol}".
            "AND node.parent = :ar_parentId{$eol}".
            "AND node.path LIKE :pathlike{$eol}".
            "AND node.path <> :path{$eol}".
            "AND BIT_AND(rights.mask, 1) = 1{$eol}".
            "AND {$eol}".
            "({$eol}".
            "resourceType.name = :type_0{$eol}".
            "OR resourceType.name = :type_1){$eol}".
            "AND node.parent IS NULL{$eol}".
            "GROUP BY node.id{$eol}";

        $this->assertEquals($expectedDql, $dql);
        $this->assertEquals(
            [
                ':workspace_id' => 123,
                ':ar_parentId' => 456,
                ':pathlike' => 'foo%',
                ':path' => 'foo',
                ':type_0' => 'baz',
                ':type_1' => 'bat',
            ],
            $qb->getParameters()
        );
    }
}
