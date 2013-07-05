<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\AltRepositoryTestCase;

class ResourceActivityRepositoryTest extends AltRepositoryTestCase
{
    public static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Resource\ResourceActivity');

        self::createUser('john');
        self::createWorkspace('ws_1');
        self::createResourceType('t_dir');
        self::createResourceType('t_file');
        self::createResourceType('t_act');
        self::createDirectory('dir_1', self::get('t_dir'), self::get('john'), self::get('ws_1'));
        self::createFile('file_1', self::get('t_file'), self::get('john'), self::get('dir_1'));
        self::createActivity(
            'activity_1',
            self::get('t_act'),
            self::get('john'),
            array(self::get('file_1')),
            self::get('dir_1')
        );
    }

    public function testFindResourceActivities()
    {
        $activityResources = self::$repo->findResourceActivities(self::get('activity_1'));
        $this->assertEquals(1, count($activityResources));
        $this->assertEquals('file_1', $activityResources[0]->getResource()->getName());
    }
}