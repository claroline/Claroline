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

class RevisionRepositoryTest extends RepositoryTestCase
{
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Resource\Revision');

        self::createUser('john');
        self::createWorkspace('ws_1');
        self::createResourceType('t_dir');
        self::createResourceType('t_text');
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createText('text_1', 3, self::get('t_text'), self::get('john'), self::get('dir_1'));
    }

    public function testGetLastRevision()
    {
        $rev = self::$repo->getLastRevision(self::get('text_1'));
        $this->assertEquals(3, $rev->getVersion());
        $this->assertEquals('text_1Content', $rev->getContent());
    }
}
