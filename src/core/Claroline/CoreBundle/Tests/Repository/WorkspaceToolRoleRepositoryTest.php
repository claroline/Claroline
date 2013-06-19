<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class WorkspaceToolRoleRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\TextRepository */
    public static $repo;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole');
        self::loadPlatformRoleData();
        self::loadUserData(array('jane' => 'user'));
    }

    public function testFindByWorkspace()
    {
        $wtr = self::$repo->findByWorkspace(self::getWorkspace('jane'));
        $this->assertTrue($wtr[0] instanceof \Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole);
        //7 from admin, 3 from user and 1 from visitor
        $this->assertEquals(11, count($wtr));
    }
}