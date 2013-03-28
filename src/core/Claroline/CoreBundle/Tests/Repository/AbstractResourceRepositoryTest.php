<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;

class AbstractResourceRepositoryTest extends FixtureTestCase
{
    private $repo;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
        $this->repo = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
    }

    public function testFindWorkspaceRoot()
    {
        $root = $this->repo->findWorkspaceRoot($this->getWorkspace('john'));
        $this->assertEquals($this->getDirectory('john'), $root);
    }

    public function testFindDescendants()
    {
        $this->loadDirectoryData('john', array('john/dir1/dir2', 'john/dir1/dir3'));
        $this->loadFileData('john', 'dir2', array('foo.txt'));

        $this->assertEquals(
            0,
            count($this->repo->findDescendants($this->getDirectory('dir3')))
        );
        $this->assertEquals(
            3,
            count($this->repo->findDescendants($this->getDirectory('dir1')))
        );
        $this->assertEquals(
            4,
            count($this->repo->findDescendants($this->getDirectory('dir1'), true))
        );
        $this->assertEquals(
            4,
            count($this->repo->findDescendants($this->getDirectory('dir1'), true))
        );
        $this->assertEquals(
            3,
            count($this->repo->findDescendants($this->getDirectory('dir1'), true, 'directory'))
        );

        $entityDirs = $this->repo->findDescendants($this->getDirectory('dir1'), false);
        $this->assertInstanceOf(
            'Claroline\CoreBundle\Entity\Resource\AbstractResource',
            $entityDirs[0]
        );
    }

    public function testFindChildren()
    {
        $this->loadDirectoryData('john', array('john/dir1/dir3', 'john/dir2'));

        $children = $this->repo->findChildren($this->getDirectory('john'), array('ROLE_ADMIN'));
        $this->assertEquals(2, count($children));
        $this->assertEquals($this->getDirectory('dir1')->getId(), $children[0]['id']);
        $this->assertEquals($this->getDirectory('dir2')->getId(), $children[1]['id']);

        $children = $this->repo->findChildren($this->getDirectory('john'), array('ROLE_ANONYMOUS', 'ROLE_FOO'));
        $this->assertEquals(0, count($children));

        $rights = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->findOneBy(array('role' => $this->getRole('anonymous'), 'resource' => $this->getDirectory('dir1')));
        $rights->setCanOpen(true);
        $this->em->persist($rights);
        $this->em->flush();
        $children = $this->repo->findChildren($this->getDirectory('john'), array('ROLE_ANONYMOUS'));
        $this->assertEquals(1, count($children));
    }

    public function testFindChildrenThrowsAnExceptionIfNoRolesAreGiven()
    {
        $this->setExpectedException('RuntimeException');
        $children = $this->repo->findChildren($this->getDirectory('john'), array());
        $this->assertEquals(0, count($children));
    }

    public function testFindWorkspaceRootsByUser()
    {
        $john = $this->getUser('john');
        $roots = $this->repo->findWorkspaceRootsByUser($john);
        $this->assertEquals(1, count($roots));
        $this->assertEquals($this->getDirectory('john')->getId(), $roots[0]['id']);

        $this->loadUserData(array('jane' => 'user'));
        $role = $this->em->getRepository('Claroline\Corebundle\Entity\Role')
            ->findCollaboratorRole($this->getWorkspace('jane'));
        $john->addRole($role);
        $this->em->persist($john);
        $this->em->flush();
        $roots = $this->repo->findWorkspaceRootsByUser($john);
        $this->assertEquals(2, count($roots));
        $this->assertEquals($this->getDirectory('jane')->getId(), $roots[0]['id']);
        $this->assertEquals($this->getDirectory('john')->getId(), $roots[1]['id']);
    }

    public function testFindAncestors()
    {
        $this->loadDirectoryData('john', array('john/dir1/dir2'));
        $ancestors = $this->repo->findAncestors($this->getDirectory('dir2'));
        $this->assertEquals(3, count($ancestors));
        $this->assertEquals($this->getDirectory('john')->getId(), $ancestors[0]['id']);
        $this->assertEquals($this->getDirectory('dir1')->getId(), $ancestors[1]['id']);
        $this->assertEquals($this->getDirectory('dir2')->getId(), $ancestors[2]['id']);
    }

    public function testFindByCriteria()
    {
        $this->loadUserData(array('jane' => 'user'));
        $timeOne = new \DateTime();
        sleep(1);
        $this->loadDirectoryData('jane', array('jane/dir1'));
        $this->loadDirectoryData('john', array('john/dir2/dir3', 'john/dir4'));
        sleep(1);
        $timeTwo = new \DateTime();
        $this->loadFileData('john', 'dir4', array('foo.txt'));

        $resources = $this->repo->findByCriteria(array());
        $this->assertEquals(7, count($resources));

        $resources = $this->repo->findByCriteria(array('types' => array('directory')));
        $this->assertEquals(6, count($resources));

        $resources = $this->repo->findByCriteria(array('roots' => array($this->getDirectory('john')->getPath())));
        $this->assertEquals(5, count($resources));

        $resources = $this->repo->findByCriteria(array('dateFrom' => $timeTwo->format('Y-m-d H:i:s')));
        $this->assertEquals(1, count($resources));

        $resources = $this->repo->findByCriteria(array('dateTo' => $timeOne->format('Y-m-d H:i:s')));
        $this->assertEquals(2, count($resources));

        $resources = $this->repo->findByCriteria(array('name' => 'j'));
        $this->assertEquals(2, count($resources));

        $resources = $this->repo->findByCriteria(array('isExportable' => true));
        $this->assertEquals(7, count($resources));

        $resources = $this->repo->findByCriteria(array(), $this->getUser('jane')->getRoles());
        $this->assertEquals(2, count($resources));

        $rights = $this->em->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceRights')
            ->findOneBy(array('role' => $this->getRole('anonymous'), 'resource' => $this->getDirectory('dir1')));
        $rights->setCanOpen(true);
        $this->em->persist($rights);
        $this->em->flush();

        $resources = $this->repo->findByCriteria(array(), array('ROLE_ANONYMOUS'));
        $this->assertEquals(1, count($resources));
        $this->assertEquals($this->getDirectory('dir1')->getId(), $resources[0]['id']);
    }

    public function testFindByCriteriaThrowsAnExceptionOnUnknownFilter()
    {
        $this->setExpectedException('Claroline\CoreBundle\Repository\Exception\UnknownFilterException');
        $this->repo->findByCriteria(array('foo' => 'bar'));
    }
}