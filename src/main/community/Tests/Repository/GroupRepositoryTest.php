<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Tests\Repository;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class GroupRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository(Group::class);
        self::createWorkspace('ws_1');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createGroup('group_1', [], [self::get('ROLE_1')]);
        self::createGroup('group_2', [], [self::get('ROLE_1')]);
        self::createGroup('group_3');
        self::createGroup('group_4');
    }

    public function testFindByWorkspace()
    {
        $groups = self::$repo->findByWorkspace(self::get('ws_1'));
        $this->assertEquals(2, count($groups));
        $this->assertEquals('group_1', $groups[0]->getName());
        $this->assertEquals('group_2', $groups[1]->getName());
    }
}
