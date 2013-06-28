<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class OrderedToolRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\OrderedToolRepository */
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        self::loadPlatformRoleData();
        self::loadUserData(array('john' => 'user', 'jane' => 'user'));
        self::loadWorkspaceData(array('ws_a' => 'john'));
    }

    public function testFindByWorkspaceAndRole()
    {
        $res = self::$repo->findByWorkspaceAndRoles(
            self::getWorkspace('jane'),
            array('ROLE_WS_MANAGER_' . self::getWorkspace('jane')->getId())
        );
        $this->assertEquals(7, count($res));
    }
}
