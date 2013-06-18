<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Claroline\CoreBundle\Entity\Logger\Log;
use Claroline\CoreBundle\Entity\Tool\WorkspaceToolRole;

class WorkspaceRepositoryTest extends RepositoryTestCase
{
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        self::loadPlatformRoleData();
        self::loadUserData(array('john' => 'user', 'jane' => 'user'));
        self::loadWorkspaceData(array('ws_a' => 'john'));

        $wtr = new WorkspaceToolRole();
        $wtr->setRole(self::$em->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ANONYMOUS'));
        $wots = self::$em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => self::getWorkspace('ws_a')));
        $wtr->setWorkspaceOrderedTool($wots[0]);

        //insert log wsread event.
        $first = new Log();
        $first->setDoer(self::getUser('john'));
        $first->setWorkspace(self::getWorkspace('john'));
        $first->setAction('ws_tool_read');
        $first->setDoerType(Log::doerTypeUser);

        $second = new Log();
        $second->setDoer(self::getUser('john'));
        $second->setWorkspace(self::getWorkspace('ws_a'));
        $second->setAction('ws_tool_read');
        $second->setDoerType(Log::doerTypeUser);

        self::$em->persist($first);
        self::$em->persist($second);
        self::$em->persist($wtr);
        self::$em->flush();
    }

    public function testFindByUser()
    {
        $workspaces = self::$repo->findByUser(self::getUser('john'));
        $this->assertEquals(2, count($workspaces));
    }

    public function testFindNonPersonal()
    {
        $workspaces = self::$repo->findNonPersonal(self::getUser('john'));
        $this->assertEquals(1, count($workspaces));
        $workspaces = self::$repo->findNonPersonal(self::getUser('jane'));
        $this->assertEquals(1, count($workspaces));
    }

    public function testFindByAnonymous()
    {
        $workspaces = self::$repo->findByAnonymous();
        $this->assertEquals(1, count($workspaces));
    }

    public function testCount()
    {
        $this->assertEquals(3, self::$repo->count());
    }

    public function testFindByRoles()
    {
        $roles = array(
            'ROLE_ANONYMOUS',
            'ROLE_WS_MANAGER_' . self::getWorkspace('jane')->getId()
        );

        $workspaces = self::$repo->findByRoles($roles);
        $this->assertEquals(2, count($workspaces));
    }

    public function testFindIdsByUserAndRoleNames()
    {
        $ids = self::$repo->findIdsByUserAndRoleNames(
            self::getUser('jane'),
            array('ROLE_WS_MANAGER')
        );

        $this->assertEquals(1, count($ids));
        $this->assertEquals(self::getWorkspace('jane')->getId(), $ids[0]['id']);

        $ids = self::$repo->findIdsByUserAndRoleNames(
            self::getUser('jane'),
            array('ROLE_NOT_EXISTING')
        );

        $this->assertEquals(0, count($ids));
    }

    public function testFindByUserAndRoleNames()
    {
         $workspaces = self::$repo->findByUserAndRoleNames(
            self::getUser('jane'),
            array('ROLE_WS_MANAGER')
        );

        $this->assertEquals(1, count($workspaces));
        $this->assertEquals(self::getWorkspace('jane')->getId(), $workspaces[0]->getId());

        $workspaces = self::$repo->findByUserAndRoleNames(
            self::getUser('jane'),
            array('ROLE_NOT_EXISTING')
        );

        $this->assertEquals(0, count($workspaces));
    }

    public function testFindLatestWorkspaceByUser()
    {
        $roles = array(
            'ROLE_WS_MANAGER_' . self::getWorkspace('ws_a')->getId(),
            'ROLE_WS_MANAGER_' . self::getWorkspace('john')->getId()
        );

        $workspaces = self::$repo->findLatestWorkspaceByUser(self::getUser('john'), $roles);
        $this->assertEquals('ws_a', $workspaces[1]['workspace']->getName());
    }
}