<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class ResourceActivityRepositoryTest extends RepositoryTestCase
{
    /** @var \Claroline\CoreBundle\Repository\ResourceActivityRepository */
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::$em->getRepository('ClarolineCoreBundle:Resource\ResourceActivity');
        self::loadPlatformRoleData();
        self::loadUserData(array('jane' => 'user'));
        self::loadFileData('jane', 'jane', array('lorem.pdf', 'sample.pdf', 'symfony.pdf'));
        self::loadActivityData(
            'activity',
            'jane',
            'jane',
            array(
                self::getFile('lorem.pdf')->getId(),
                self::getFile('sample.pdf')->getId(),
                self::getFile('symfony.pdf')->getId()
            )
        );
    }

    public function testFindResourceActivities()
    {
        $ra = self::$repo->findResourceActivities(self::getActivity('activity'));
        $this->assertEquals(3, count($ra));
    }
}