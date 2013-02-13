<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class TextControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user'));
        $this->client->followRedirects();
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($this->getFixtureReference('user/user')->getPersonalWorkspace());
    }

    public function testAdd()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr->getId());
        $this->assertEquals('This is a text', $text->name);
    }

    public function testDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr->getId());
        $crawler = $this->client->request('GET', "/resource/open/text/{$text->id}");
        $node = $crawler->filter('#text_content');
        $this->assertTrue(strpos($node->text(), 'hello world') !== false);
    }

    public function testEditByRefAction()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr->getId());
        $crawler = $this->client->request('GET', "/text/form/edit/{$text->id}");
        $form = $crawler->filter('button[type=submit]')->form();
        $crawler = $this->client->submit($form, array('content' => 'the answer is 42'));
        $crawler = $this->client->request('GET', "/resource/open/text/{$text->id}");
        $node = $crawler->filter('#text_content');
        $this->assertTrue(strpos($node->text(), 'the answer is 42') != false);
        $textId = $text->{'id'};
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($textId);
        $revisions = $text->getRevisions();
        $this->assertEquals(2, count($revisions));
    }

    public function testCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'resource/form/text');
        $form = $crawler->filter('#text_form');
        $this->assertEquals(count($form), 1);
    }

    public function testFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request(
            'POST', "/resource/create/text/{$this->pwr->getId()}"
        );

        $form = $crawler->filter('#text_form');
        $this->assertEquals(count($form), 1);
    }

    private function addText($name, $text, $parentId)
    {
        $this->client->request(
            'POST',
            "/resource/create/text/{$parentId}",
            array('text_form' => array('name' => $name, 'text' => $text))
        );

        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
    }
}