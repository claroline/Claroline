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
     * @expectedException Claroline\CoreBundle\Repository\Exception\MissingSelectClauseException
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
            "LEFT JOIN node.next next{$eol}".
            "LEFT JOIN node.previous previous{$eol}".
            "LEFT JOIN node.parent parent{$eol}".
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
            "    node.name as name,{$eol}".
            "    node.path as path,{$eol}".
            "    parent.id as parent_id,{$eol}".
            "    creator.username as creator_username,{$eol}".
            "    resourceType.name as type,{$eol}".
            "    previous.id as previous_id,{$eol}".
            "    next.id as next_id,{$eol}".
            "    icon.relativeUrl as large_icon,{$eol}".
            "    node.mimeType as mime_type{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.next next{$eol}".
            "LEFT JOIN node.previous previous{$eol}".
            "LEFT JOIN node.parent parent{$eol}".
            "LEFT JOIN node.icon icon{$eol}";

        $this->assertEquals($expectedDql, $dql);

        $dql = $qb->selectAsArray(true)->getDql();
        $expectedDql =
            "SELECT DISTINCT{$eol}".
            "    node.id as id,{$eol}".
            "    node.name as name,{$eol}".
            "    node.path as path,{$eol}".
            "    parent.id as parent_id,{$eol}".
            "    creator.username as creator_username,{$eol}".
            "    resourceType.name as type,{$eol}".
            "    previous.id as previous_id,{$eol}".
            "    next.id as next_id,{$eol}".
            "    icon.relativeUrl as large_icon,{$eol}".
            '    node.mimeType as mime_type'.
            ",{$eol}rights.mask{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.next next{$eol}".
            "LEFT JOIN node.previous previous{$eol}".
            "LEFT JOIN node.parent parent{$eol}".
            "LEFT JOIN node.icon icon{$eol}".
            "LEFT JOIN node.rights rights{$eol}".
            "JOIN rights.role rightRole{$eol}";
        $this->assertEquals($expectedDql, $dql);
    }

    public function testFilters()
    {
        $qb = new ResourceQueryBuilder();

        $mockedWorkspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $mockedWorkspace->shouldReceive('getId')->once()->andReturn(123);
        $mockedParent = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $mockedParent->shouldReceive('getId')->once()->andReturn(456);
        $mockedUser = $this->mock('Claroline\CoreBundle\Entity\User');
        $mockedUser->shouldReceive('getId')->once()->andReturn(789);

        $dql = $qb->selectAsEntity(true)
            ->whereInWorkspace($mockedWorkspace)
            ->whereParentIs($mockedParent)
            ->wherePathLike('foo', false)
            ->whereRoleIn(array('ROLE_FOO', 'ROLE_BAR'))
            ->whereCanOpen()
            ->whereInUserWorkspace($mockedUser)
            ->whereTypeIn(array('baz', 'bat'))
            ->whereRootIn(array('foo-root', 'bar-root'))
            ->whereDateFrom('2013-03-01')
            ->whereDateTo('2013-04-01')
            ->whereNameLike('foobar')
            ->whereIsExportable(true)
            ->whereParentIsNull()
            ->orderByPath()
            ->groupById()
            ->getDql();

        $eol = PHP_EOL;
        $expectedDql =
            "SELECT node{$eol}".
            "FROM Claroline\CoreBundle\Entity\Resource\ResourceNode node{$eol}".
            "JOIN node.creator creator{$eol}".
            "JOIN node.resourceType resourceType{$eol}".
            "LEFT JOIN node.next next{$eol}".
            "LEFT JOIN node.previous previous{$eol}".
            "LEFT JOIN node.parent parent{$eol}".
            "LEFT JOIN node.icon icon{$eol}".
            "LEFT JOIN node.rights rights{$eol}".
            "JOIN rights.role rightRole{$eol}".
            "WHERE node.workspace = :workspace_id{$eol}".
            "AND node.parent = :ar_parentId{$eol}".
            "AND node.path LIKE :pathlike{$eol}".
            "AND node.path <> :path{$eol}".
            "AND {$eol}".
            "({$eol}".
            "    rightRole.name = :role_0{$eol}".
            "    OR rightRole.name = :role_1{$eol}".
            "){$eol}".
            "AND BIT_AND(rights.mask, 1) = 1{$eol}".
            "AND node.workspace IN{$eol}".
            "({$eol}".
            "    SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\Workspace aw{$eol}".
            "    JOIN aw.roles r{$eol}".
            "    WHERE r IN (SELECT r2 FROM Claroline\CoreBundle\Entity\Role r2 {$eol}".
            "       LEFT JOIN r2.users u {$eol}".
            "       LEFT JOIN r2.groups g {$eol}".
            "       LEFT JOIN g.users u2 {$eol}".
            "       WHERE u.id = :user_id OR u2.id = :user_id {$eol}".
            "   ) {$eol}".
            ") {$eol}{$eol}".
            "AND resourceType.name = :type_0{$eol}".
            "OR resourceType.name = :type_1{$eol}".
            "AND {$eol}".
            "({$eol}".
            "    node.path LIKE :root_0{$eol}".
            "    OR node.path LIKE :root_1{$eol}".
            "){$eol}".
            "AND node.creationDate >= :dateFrom{$eol}".
            "AND node.creationDate <= :dateTo{$eol}".
            "AND node.name LIKE :name{$eol}".
            "AND resourceType.isExportable = :isExportable{$eol}".
            "AND node.parent IS NULL{$eol}".
            "ORDER BY node.path{$eol}".
            "GROUP BY node.id{$eol}";

        $this->assertEquals($expectedDql, $dql);
        $this->assertEquals(
            array(
                ':workspace_id' => 123,
                ':ar_parentId' => 456,
                ':pathlike' => 'foo%',
                ':path' => 'foo',
                ':role_0' => 'ROLE_FOO',
                ':role_1' => 'ROLE_BAR',
                ':user_id' => 789,
                ':type_0' => 'baz',
                ':type_1' => 'bat',
                ':root_0' => 'foo-root%',
                ':root_1' => 'bar-root%',
                ':dateFrom' => '2013-03-01',
                ':dateTo' => '2013-04-01',
                ':name' => '%foobar%',
                ':isExportable' => true,
            ),
            $qb->getParameters()
        );
    }
}
