<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceRightsRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository(ResourceRights::class);

        self::createWorkspace('ws_1');
        self::createRole('ROLE_ADMIN');
        self::createRole('ROLE_1', self::get('ws_1'));
        self::createRole('ROLE_2', self::get('ws_1'));
        self::createUser('john', [self::get('ROLE_1')]);
        self::createResourceType('t_dir', 'Directory');
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createDirectory('dir_2', self::get('t_dir'), self::get('john'), self::get('ws_1'), self::get('dir_1'));
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_1'), 3);
        self::createResourceRights(self::get('ROLE_1'), self::get('dir_2'), 1);
        self::createResourceRights(self::get('ROLE_2'), self::get('dir_1'), 33, [self::get('t_dir')]);
    }

    public function testFindMaximumRights()
    {
        $mask = self::$repo->findMaximumRights(['ROLE_1'], self::get('dir_1')->getResourceNode());
        $this->assertTrue(0 !== (1 & $mask));
        $this->assertTrue(0 !== (2 & $mask));
        $mask = self::$repo->findMaximumRights(['ROLE_1', 'ROLE_2'], self::get('dir_1')->getResourceNode());
        $this->assertTrue(0 !== (32 & $mask));
        $this->assertTrue(0 !== (1 & $mask));
        $this->assertTrue(0 !== (2 & $mask));
    }

    public function testFindCreationRights()
    {
        $creationRights = self::$repo->findCreationRights(['ROLE_1'], self::get('dir_1')->getResourceNode());
        $this->assertEquals(0, count($creationRights));
        $creationRights = self::$repo->findCreationRights(
            ['ROLE_1', 'ROLE_2'],
            self::get('dir_1')->getResourceNode()
        );
        $this->assertEquals(1, count($creationRights));
        $this->assertEquals('t_dir', $creationRights[0]['name']);
    }
}
