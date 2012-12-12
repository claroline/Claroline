<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

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
        $this->loadUserFixture(array('user'));
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
    }

    public function testCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'resource/form/activity');
        $form = $crawler->filter('#activity_form');
        $this->assertEquals(count($form), 1);
    }

    public function testFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
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
        $this->logUser($this->getFixtureReference('user/user'));
        $this->createActivity('name', 'instruction');
        $this->client->request('GET', "/resource/children/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals(1, count($dir));
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
}

