<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceRightsRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Resource\ResourceRights');

        self::createWorkspace('ws_1');
        self::createRole('ROLE_ADMIN');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_1'));
        self::createUser('john', array(self::get('ROLE_1')));
        self::createResourceType('t_dir');
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createDirectory('dir_2', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_1'), array('open'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_2'), array('open'));
        self::createResourceRights(self::get('ROLE_2'), self::get('dir_1'), array('edit'), array(self::get('t_dir')));
    }

    public function testFindMaximumRights()
    {
        $rights = self::$repo->findMaximumRights(array('ROLE_1'), self::get('dir_1'));
        $this->assertEquals(0, $rights['canEdit']);
        $rights = self::$repo->findMaximumRights(array('ROLE_1', 'ROLE_2'), self::get('dir_1'));
        $this->assertEquals(1, $rights['canEdit']);
    }

    public function testFindCreationRights()
    {
        $creationRights = self::$repo->findCreationRights(array('ROLE_1'), self::get('dir_1'));
        $this->assertEquals(0, count($creationRights));
        $creationRights = self::$repo->findCreationRights(array('ROLE_1', 'ROLE_2'), self::get('dir_1'));
        $this->assertEquals(1, count($creationRights));
        $this->assertEquals('t_dir', $creationRights[0]['name']);
    }

    public function testFindNonAdminRights()
    {
        $this->markTestSkipped('That method will disappear soon');
    }

    public function testFindRecursiveByResource()
    {
        $rights =  self::$repo->findRecursiveByResource(self::get('dir_1'));
        $this->assertEquals(3, count($rights));
    }

    public function testFindRecursiveByResourceAndRole()
    {
        $rights =  self::$repo->findRecursiveByResourceAndRole(self::get('dir_1'), self::get('ROLE_1'));
        $this->assertEquals(2, count($rights));
    }
}
