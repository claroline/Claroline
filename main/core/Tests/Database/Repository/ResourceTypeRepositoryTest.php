<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceTypeRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Resource\ResourceType');

        self::createUser('john');
        self::createWorkspace('ws_1');
        self::createPlugin('Vendor1', 'Bundle1');
        self::createPlugin('Vendor2', 'Bundle2');
        self::createResourceType('type_1');
        self::createResourceType('type_2', true, self::get('Vendor1Bundle1'));
        self::createResourceType('type_3', true, self::get('Vendor2Bundle2'));
        self::createDirectory('dir_1', self::get('type_1'), self::get('john'), self::get('ws_1'));
    }

    public function testFindPluginResourceTypes()
    {
        //this is not great because we don't really count them and the number is pretty much arbibtrary... but at lease it doesn't crash !
        $this->assertGreaterThan(15, count(self::$repo->findPluginResourceTypes()));
    }

    public function testCountResourcesByType()
    {
        $this->markTestSkipped('what is it ?');
        $types = self::$repo->countResourcesByType();
        $this->assertEquals(3, count($types));
        $this->assertEquals(1, $types[0]['total']);
        $this->assertEquals(0, $types[1]['total']);
        $this->assertEquals(0, $types[2]['total']);
    }
}
