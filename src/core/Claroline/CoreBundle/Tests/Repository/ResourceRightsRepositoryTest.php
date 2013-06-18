<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceRightsRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\ResourceRightsRepository */
    public static $repo;
    public static $collaboratorRoleName;
    public static $managerRoleName;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::loadPlatformRoleData();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Resource\ResourceRights');
        self::loadUserData(array('john' => 'user'));
        self::loadDirectoryData('john', array('john/dir1'));
        self::$collaboratorRoleName = 'ROLE_WS_COLLABORATOR_'.self::getWorkspace('john')->getId();
        self::$managerRoleName = 'ROLE_WS_MANAGER_'.self::getWorkspace('john')->getId();
    }

    public function testFindMaximumRights()
    {
        $rights = self::$repo->findMaximumRights(
            array(self::$collaboratorRoleName, self::$managerRoleName), self::getDirectory('john')
        );
        $this->assertEquals(1, $rights['canEdit']);
        $rights = self::$repo->findMaximumRights(array(self::$collaboratorRoleName), self::getDirectory('john'));
        $this->assertEquals(0, $rights['canEdit']);
    }

    public function testFindCreationRights()
    {
        $creationRights = self::$repo->findCreationRights(array(self::$collaboratorRoleName), self::getDirectory('john'));
        $this->assertEquals(0, count($creationRights));
        $creationRights = self::$repo->findCreationRights(array(self::$managerRoleName), self::getDirectory('john'));
        $this->assertEquals(5, count($creationRights));
        $creationRights = self::$repo->findCreationRights(
            array(self::$managerRoleName, self::$collaboratorRoleName), self::getDirectory('john')
        );
        $this->assertEquals(5, count($creationRights));
    }

    public function testFindNonAdminRights()
    {
        $rights = self::$repo->findNonAdminRights(self::getDirectory('john'));
        $this->assertEquals(4, count($rights));
    }

    public function testFindRecursiveByResource()
    {
        $rights =  self::$repo->findRecursiveByResource(self::getDirectory('john'));
        $this->assertEquals(10, count($rights));
    }

    public function testFindRecursiveByResourceAndRole()
    {
        $rights =  self::$repo->findRecursiveByResourceAndRole(
            self::getDirectory('john'),
            self::$em->getRepository('ClarolineCoreBundle:Role')->findOneByName(self::$collaboratorRoleName)
        );
        $this->assertEquals(2, count($rights));
    }
}