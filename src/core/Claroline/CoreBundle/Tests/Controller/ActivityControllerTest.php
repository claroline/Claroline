<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ActivityControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('admin'));
        $this->resourceRepository = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $this->pwr = $this->resourceRepository->getRootForWorkspace($this->getFixtureReference('user/admin')->getPersonalWorkspace());

    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testAddThenRemoveResource()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $repo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $file = $this->uploadFile($this->pwr->getId(), 'file');
        $activity = $this->createActivity('name', 'instruction');
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$file->id}"
        );
        $obj = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($obj));
        $resourceActivity = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceActivity')->findOneBy(array('activity' => $activity->id));
        $this->assertEquals(1, count($resourceActivity));
//       the code below doesn't work: no idea why
        $this->client->request(
            'DELETE',
            "/activity/{$activity->id}/remove/resource/{$file->id}"
        );
        $this->client->getContainer()->get('doctrine.orm.entity_manager')->flush();
        $resourceActivity = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceActivity')->findOneBy(array('activity' => $activity->id));
        $this->assertEquals(0, count($resourceActivity));
    }

    public function testSequenceOrder()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $repo = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $fileOne = $this->uploadFile($this->pwr->getId(), 'file1');
        $fileTwo= $this->uploadFile($this->pwr->getId(), 'file2');
        $activity = $this->createActivity('name', 'instruction');
        $activityEntity = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\Activity')->find($activity->id);
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$fileOne->id}"
        );
        $this->client->request(
            'POST',
            "/activity/{$activity->id}/add/resource/{$fileTwo->id}"
        );

       $resourceActivities = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceActivity')->getResourcesActivityForActivity($activityEntity);

       foreach($resourceActivities as $resourceActivity){
           $orders[] = $resourceActivity->getSequenceOrder();
           $ids[] = $resourceActivity->getResource()->getId();
       }

       $this->assertEquals(array('0', '1'), $orders);

       $this->client->request(
           'GET', "/activity/{$activity->id}/set/sequence?ids[]={$ids[1]}&ids[]={$ids[0]}"
       );

       $reverseActivities = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceActivity')->getResourcesActivityForActivity($activityEntity);

        foreach ($reverseActivities as $reverseActivity) {

            $reverseIds[] = $reverseActivity->getResource()->getId();
        }

        $this->assertEquals($ids, array_reverse($reverseIds));

    }

    private function createActivity($name, $instruction)
    {
        $this->client->request(
            'POST',
            "/resource/create/activity/{$this->pwr->getId()}",
            array('activity_form' => array('name' => $name, 'instructions' => $instruction))
        );

        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
    }

    private function uploadFile($parentId, $name, $shareType = 1)
    {
        $file = new UploadedFile(tempnam(sys_get_temp_dir(), 'FormTest'), $name, 'text/plain', null, null, true);
        $this->client->request(
            'POST', "/resource/create/file/{$parentId}", array('file_form' => array()), array('file_form' => array('file' => $file, 'name' => 'tmp'))
        );

        $obj = json_decode($this->client->getResponse()->getContent());
        return $obj[0];
    }
}
