<?php

namespace Claroline\CoreBundle\Repository;

class ResourceQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testASelectClauseIsRequired()
    {
        $this->setExpectedException('Claroline\CoreBundle\Repository\Exception\MissingSelectClauseException');
        $qb = new ResourceQueryBuilder();
        $qb->getDql();
    }

    public function testSelectAsEntity()
    {
        $qb = new ResourceQueryBuilder();

        $dql = $qb->selectAsEntity()->getDql();
        $eol = PHP_EOL;
        $expectedDql =
            "SELECT resource{$eol}" .
            "FROM Claroline\CoreBundle\Entity\Resource\AbstractResource resource{$eol}";
        $this->assertEquals($expectedDql, $dql);

        $dql = $qb->selectAsEntity(true)->getDql();
        $expectedDql =
            "SELECT resource{$eol}" .
            "FROM Claroline\CoreBundle\Entity\Resource\AbstractResource resource{$eol}" .
            "JOIN resource.creator creator{$eol}" .
            "JOIN resource.resourceType resourceType{$eol}" .
            "JOIN resource.icon icon{$eol}";
        $this->assertEquals($expectedDql, $dql);
    }

    public function testSelectAsArray()
    {
        $qb = new ResourceQueryBuilder();

        $dql = $qb->selectAsArray()->getDql();
        $eol = PHP_EOL;
        $expectedDql =
            "SELECT DISTINCT{$eol}" .
            "    resource.id as id,{$eol}" .
            "    resource.name as name,{$eol}" .
            "    resource.path as path,{$eol}" .
            "    IDENTITY(resource.parent) as parent_id,{$eol}" .
            "    creator.username as creator_username,{$eol}" .
            "    resourceType.name as type,{$eol}" .
            "    resourceType.isBrowsable as is_browsable,{$eol}" .
            "    icon.relativeUrl as large_icon{$eol}" .
            "FROM Claroline\CoreBundle\Entity\Resource\AbstractResource resource{$eol}" .
            "JOIN resource.creator creator{$eol}" .
            "JOIN resource.resourceType resourceType{$eol}" .
            "JOIN resource.icon icon{$eol}";
        $this->assertEquals($expectedDql, $dql);

        $dql = $qb->selectAsArray(true)->getDql();
        $expectedDql =
            "SELECT DISTINCT{$eol}" .
            "    resource.id as id,{$eol}" .
            "    resource.name as name,{$eol}" .
            "    resource.path as path,{$eol}" .
            "    IDENTITY(resource.parent) as parent_id,{$eol}" .
            "    creator.username as creator_username,{$eol}" .
            "    resourceType.name as type,{$eol}" .
            "    resourceType.isBrowsable as is_browsable,{$eol}" .
            "    icon.relativeUrl as large_icon,{$eol}" .
            "    MAX (CASE rights.canExport WHEN true THEN 1 ELSE 0 END) as can_export,{$eol}" .
            "    MAX (CASE rights.canDelete WHEN true THEN 1 ELSE 0 END) as can_delete,{$eol}" .
            "    MAX (CASE rights.canEdit WHEN true THEN 1 ELSE 0 END) as can_edit{$eol}" .
            "FROM Claroline\CoreBundle\Entity\Resource\AbstractResource resource{$eol}" .
            "JOIN resource.creator creator{$eol}" .
            "JOIN resource.resourceType resourceType{$eol}" .
            "JOIN resource.icon icon{$eol}" .
            "LEFT JOIN resource.rights rights{$eol}" .
            "JOIN rights.role rightRole{$eol}";
        $this->assertEquals($expectedDql, $dql);
    }

    public function testFilters()
    {
        $qb = new ResourceQueryBuilder();

        $mockedWorkspace = $this->getMock('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace');
        $mockedWorkspace->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(123));
        $mockedParent = $this->getMock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $mockedParent->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(456));
        $mockedUser = $this->getMock('Claroline\CoreBundle\Entity\User');
        $mockedUser->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(789));

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
            "SELECT resource{$eol}" .
            "FROM Claroline\CoreBundle\Entity\Resource\AbstractResource resource{$eol}" .
            "JOIN resource.creator creator{$eol}" .
            "JOIN resource.resourceType resourceType{$eol}" .
            "JOIN resource.icon icon{$eol}" .
            "LEFT JOIN resource.rights rights{$eol}" .
            "JOIN rights.role rightRole{$eol}" .
            "WHERE resource.workspace = :workspace_id{$eol}" .
            "AND resource.parent = :ar_parentId{$eol}" .
            "AND resource.path LIKE :pathlike{$eol}" .
            "AND resource.path <> :path{$eol}" .
            "AND {$eol}" .
            "({$eol}" .
            "    rightRole.name = :role_0{$eol}" .
            "    OR rightRole.name = :role_1{$eol}" .
            "){$eol}" .
            "AND rights.canOpen = true{$eol}" .
            "AND resource.workspace IN{$eol}" .
            "({$eol}" .
            "    SELECT aw FROM Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace aw{$eol}" .
            "    JOIN aw.roles r{$eol}" .
            "    JOIN r.users u{$eol}" .
            "    WHERE u.id = :user_id{$eol}" .
            "){$eol}" .
            "AND resourceType.name = :type_0{$eol}" .
            "OR resourceType.name = :type_1{$eol}" .
            "AND {$eol}" .
            "({$eol}" .
            "    resource.path LIKE :root_0{$eol}" .
            "    OR resource.path LIKE :root_1{$eol}" .
            "){$eol}" .
            "AND resource.creationDate >= :dateFrom{$eol}" .
            "AND resource.creationDate <= :dateTo{$eol}" .
            "AND resource.name LIKE :name{$eol}" .
            "AND resourceType.isExportable = :isExportable{$eol}" .
            "AND resource.parent IS NULL{$eol}" .
            "ORDER BY resource.path{$eol}" .
            "GROUP BY resource.id{$eol}";

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
                ':isExportable' => true
            ),
            $qb->getParameters()
        );
    }
}