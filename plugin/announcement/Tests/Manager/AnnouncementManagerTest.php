<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Manager;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class AnnouncementManagerTest extends MockeryTestCase
{
    private $announcementRepo;
    private $om;

    public function setUp()
    {
        parent::setUp();
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->announcementRepo = $this->mock('Claroline\AnnouncementBundle\Repository\AnnouncementRepository');
    }

    public function testInsertAnnouncement()
    {
        $announcement = new Announcement();

        $this->om->shouldReceive('persist')
            ->with($announcement)
            ->once();
        $this->om->shouldReceive('flush')
            ->once();

        $this->getManager()->insertAnnouncement($announcement);
    }

    public function testDeleteAnnouncement()
    {
        $announcement = new Announcement();

        $this->om->shouldReceive('remove')
            ->with($announcement)
            ->once();
        $this->om->shouldReceive('flush')
            ->once();

        $this->getManager()->deleteAnnouncement($announcement);
    }

    public function testGetVisibleAnnouncementsByWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $roleA = new Role();
        $roleB = new Role();
        $roles = [$roleA, $roleB];
        $announcements = ['announ_1', 'announ_2'];

        $this->announcementRepo
            ->shouldReceive('findVisibleAnnouncementsByWorkspace')
            ->with($workspace, $roles)
            ->once()
            ->andReturn($announcements);

        $this->assertEquals(
            $announcements,
            $this->getManager()->getVisibleAnnouncementsByWorkspace($workspace, $roles)
        );
    }

    public function testGetVisibleAnnouncementsByWorkspaces()
    {
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaces = [$workspaceA, $workspaceB];
        $roleA = new Role();
        $roleB = new Role();
        $roles = [$roleA, $roleB];
        $announcements = ['announ_1', 'announ_2'];

        $this->announcementRepo
            ->shouldReceive('findVisibleAnnouncementsByWorkspaces')
            ->with($workspaces, $roles)
            ->once()
            ->andReturn($announcements);

        $this->assertEquals(
            $announcements,
            $this->getManager()->getVisibleAnnouncementsByWorkspaces($workspaces, $roles)
        );
    }

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineAnnouncementBundle:Announcement')
            ->once()
            ->andReturn($this->announcementRepo);

        if (count($mockedMethods) === 0) {
            return new AnnouncementManager($this->om);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\AnnouncementBundle\Manager\AnnouncementManager'.$stringMocked,
            [$this->om]
        );
    }
}
