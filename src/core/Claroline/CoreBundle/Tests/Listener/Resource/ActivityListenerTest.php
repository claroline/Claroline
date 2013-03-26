<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadActivityData;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;

class ActivityListenerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    /** @var string */
    private $stubDir;

    /** @var $ResourceInstance */
    private $pwr;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
        $this->pwr = $this->getDirectory('user');
    }

    public function testCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', 'resource/form/activity');
        $form = $crawler->filter('#activity_form');
        $this->assertEquals(count($form), 1);
    }

    public function testFormErrorsAreDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'POST',
            "/resource/create/activity/{$this->pwr->getId()}",
            array()
        );

        $form = $crawler->filter('#activity_form');
        $this->assertEquals(count($form), 1);
    }

    public function testCreateActivity()
    {
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'POST',
            "/resource/create/activity/{$this->pwr->getId()}",
            array('activity_form' => array('name' => 'name', 'instructions' => 'instructions'))
        );

        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
    }

    public function testCopyActivity()
    {
        $this->loadFileData('user', 'user', array('foo.txt', 'bar.txt'));
        $this->createActivity(
            'activity',
            'user',
            'user',
            array($this->getFile('foo.txt'), $this->getFile('bar.txt'))
        );

        $event = new CopyResourceEvent($this->getActivity('activity'));
        $this->client->getContainer()->get('event_dispatcher')->dispatch('copy_activity', $event);
        $this->assertEquals(1, count($event->getCopy()));
        $this->assertEquals(2, count($event->getResource()->getResourceActivities()));
    }

    public function testExportTemplate()
    {
        $this->loadFileData('user', 'user', array('foo.txt', 'bar.txt'));
        $this->createActivity(
            'activity',
            'user',
            'user',
            array($this->getFile('foo.txt'), $this->getFile('bar.txt'))
        );

        $event = new ExportResourceTemplateEvent($this->getActivity('activity'));
        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_activity_to_template', $event);
        $config = $event->getConfig();
        $this->assertEquals(2, count($config));
        $this->assertEquals(2, count($config['resources']));
    }

    public function testImportTemplate()
    {
        $this->loadFileData('user', 'user', array('foo.txt', 'bar.txt'));

        $activity = array (
            'instructions' => 'Hello world!',
            'resources' => array(array('id' => 1, 'order' => 1), array('id' => 2, 'order' => 2 ))
        );

        $event = new ImportResourceTemplateEvent(
            $activity,
            $this->getDirectory('user'),
            $this->getUser('user')
        );

        $event->addCreatedResource($this->getFile('foo.txt'), 1);
        $event->addCreatedResource($this->getFile('bar.txt'), 2);

        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_activity_from_template', $event);
        $activity = $event->getResource();
        $this->assertEquals($activity->getInstructions(), 'Hello world!');
        $this->assertEquals(count($activity->getResourceActivities()), 2);
    }

    private function createActivity($name, $parent, $author, array $resources)
    {
        foreach ($resources as $resource) {
            $ids[] = $resource->getId();
        }

        $this->loadFixture(new LoadActivityData($name, $parent, $author, $ids));
    }
}

