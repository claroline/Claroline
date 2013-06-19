<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;
use Claroline\CoreBundle\Entity\Resource\Revision;

class TextRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\TextRepository */
    public static $repo;


    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Resource\Text');
        self::loadPlatformRoleData();
        self::loadUserData(array('jane' => 'user'));
        self::loadTextData('jane', 'jane', 200, array('text'));
        $rev = new Revision();
        $rev->setContent('content');
        $rev->setUser(self::getUser('jane'));
        $rev->setVersion(2);
        $rev->setText(self::getText('text'));
        self::$em->persist($rev);
        self::$em->flush();
    }

    public function testGetLastRevision()
    {
        $rev = self::$repo->getLastRevision(self::getText('text'));
        $this->assertEquals(2, $rev->getVersion());
        $this->assertEquals('content', $rev->getContent());
    }
}