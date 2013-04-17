<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ActivityControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('admin' => 'admin'));
        $this->resourceRepository = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource');
    }

    public function testAddThenRemoveResource()
    {
        $this->loadFileData('admin', 'admin', array('foo.txt'));
        $file = $this->getFile('foo.txt');
        $this->logUser($this->getUser('admin'));
        $activity = $this->createActivity('name', 'instruction');
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$file->getId()}"
        );
        $obj = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($obj));
        $resourceActivity = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findOneBy(array('activity' => $activity->id));
        $this->assertEquals(1, count($resourceActivity));
        //the code below doesn't work: no idea why
        $this->client->request(
            'DELETE',
            "/activity/{$activity->id}/remove/resource/{$file->getId()}"
        );
        $this->client->getContainer()->get('doctrine.orm.entity_manager')->flush();
        $resourceActivity = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findOneBy(array('activity' => $activity->id));
        $this->assertEquals(0, count($resourceActivity));
    }

    public function testSequenceOrder()
    {
        $this->loadFileData('admin', 'admin', array('foo.txt'));
        $this->loadFileData('admin', 'admin', array('bar.txt'));
        $fileOne = $this->getFile('foo.txt');
        $fileTwo = $this->getFile('bar.txt');
        $this->logUser($this->getUser('admin'));
        $activity = $this->createActivity('name', 'instruction');
        $activityEntity = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\Activity')
            ->find($activity->id);
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$fileOne->getId()}"
        );
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$fileTwo->getId()}"
        );

        $resourceActivities = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activityEntity);

        foreach ($resourceActivities as $resourceActivity) {
            $orders[] = $resourceActivity->getSequenceOrder();
            $ids[] = $resourceActivity->getResource()->getId();
        }

        $this->assertEquals(array('0', '1'), $orders);

        $this->client->request(
            'GET', "/activity/{$activity->id}/set/sequence?ids[]={$ids[1]}&ids[]={$ids[0]}"
        );

        $reverseActivities = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceActivity')
            ->findResourceActivities($activityEntity);

        foreach ($reverseActivities as $reverseActivity) {

            $reverseIds[] = $reverseActivity->getResource()->getId();
        }

        $this->assertEquals($ids, array_reverse($reverseIds));
    }

    public function testShowPlayer()
    {
        $this->loadFileData('admin', 'admin', array('foo.txt'));
        $this->loadFileData('admin', 'admin', array('bar.txt'));
        $fileOne = $this->getFile('foo.txt');
        $fileTwo = $this->getFile('bar.txt');
        $this->logUser($this->getUser('admin'));
        $activity = $this->createActivity('name', 'instruction');
        $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\Activity')
            ->find($activity->id);
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$fileOne->getId()}"
        );
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$fileTwo->getId()}"
        );
        $crawler = $this->client->request(
            'GET', "/activity/player/{$activity->id}"
        );
        $this->assertEquals(1, count($crawler->filter('#left-frame')));
        $this->assertEquals(1, count($crawler->filter('#right-frame')));
    }

    private function createActivity($name, $instruction)
    {
        $this->client->request(
            'POST',
            "/resource/create/activity/{$this->getFile('foo.txt')->getId()}",
            array('activity_form' => array('name' => $name, 'instructions' => $instruction))
        );
        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
    }
}
