<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;

class TextManagerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->client->followRedirects();
    }

    public function testAdd()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $id = $this->addText('Hello world');
        $crawler = $this->client->request('GET', '/resource/directory');
        $this->assertEquals(1, $crawler->filter('.row_resource')->count());
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($id)->getResource();
        $this->assertEquals('Hello world', $text->getLastRevision()->getContent());
        $this->assertEquals(1, count($text->getRevisions()));
        $this->assertEquals(1, count($text->getLastRevision()));
    }

    public function testDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $id = $this->addText('Hello world');
        $personnalWsUserId = $this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId();
        $crawler = $this->client->request('GET', "/resource/click/{$id}/{$personnalWsUserId}");
        $node = $crawler->filter('#content');

        $this->assertTrue(strpos($node->text(), 'Hello world') !== false);
    }

    public function testEditByRefAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $personnalWsUserId = $this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId();
        $id = $this->addText('Hello world');
        $crawler = $this->client->request('GET', "/resource/edit/{$id}/{$this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId()}/ref");
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('content' => 'the answer is 42'));
        $crawler = $this->client->request('GET', "/resource/click/{$id}/{$personnalWsUserId}");
        $node = $crawler->filter('#content');
        $this->assertTrue(strpos($node->text(), 'the answer is 42')!=false);
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($id)->getResource();
        $revisions = $text->getRevisions();
        $this->assertEquals(2, count($revisions));
    }

    private function addText($txt)
    {
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $form = $crawler->filter('input[type=submit]')->form();
        $fileTypeId = $this->getFixtureReference('resource_type/text')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('text_form[text]' => $txt));
        $id = $crawler->filter(".row_resource")->last()->attr('data-resource_instance_id');

        return $id;
    }
}