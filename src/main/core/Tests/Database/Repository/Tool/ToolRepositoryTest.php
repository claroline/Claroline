<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Tool;

use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ToolRepositoryTest extends RepositoryTestCase
{
    /** @var ToolRepository */
    private static $repo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$repo = self::getRepository(Tool::class);

        self::createUser('john');
        self::createWorkspace('ws_1');
        self::createTool('tool_1');
        self::createTool('tool_2');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_1'));
        self::createWorkspaceTool(self::get('tool_1'), self::get('ws_1'), [self::get('ROLE_1')], 1);
        self::createWorkspaceTool(self::get('tool_2'), self::get('ws_1'), [self::get('ROLE_2')], 1);
        self::createDesktopTool(self::get('tool_2'), self::get('john'), 1);
    }

    public function testFindUndisplayedToolsByWorkspace()
    {
        $result = self::$repo->findUndisplayedToolsByWorkspace(self::get('ws_1'));
        $toolNames = array_map(function (Tool $tool) {
            return $tool->getName();
        }, $result);

        // I cannot check the number of tools returned because it can change over time.
        // Also the claroline instance can contain plugins which inject tools from the outside of the distribution bundle.
        // Instead I will just check for some of the expected tools from the core
        $this->assertTrue(in_array('home', $toolNames));
        $this->assertTrue(in_array('community', $toolNames));
        $this->assertTrue(in_array('resources', $toolNames));
    }

    public function testCountDisplayedToolsByWorkspace()
    {
        $this->assertEquals(2, self::$repo->countDisplayedToolsByWorkspace(self::get('ws_1')));
    }
}
