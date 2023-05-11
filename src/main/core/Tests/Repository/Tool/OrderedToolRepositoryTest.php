<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Tests\Repository\Tool;

use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class OrderedToolRepositoryTest extends RepositoryTestCase
{
    public static $repo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository(OrderedTool::class);

        self::createWorkspace('ws_1');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_1'));
        self::createTool('tool_1');
        self::createTool('tool_2');
        self::createWorkspaceTool(self::get('tool_1'), self::get('ws_1'), [self::get('ROLE_1')], 1);
        self::createWorkspaceTool(self::get('tool_2'), self::get('ws_1'), [self::get('ROLE_2')], 1);
    }

    public function testFindByWorkspaceAndRole()
    {
        $tools = self::$repo->findByWorkspaceAndRoles(self::get('ws_1'), ['ROLE_1', 'ROLE_2']);
        $this->assertEquals(2, count($tools));
    }
}
