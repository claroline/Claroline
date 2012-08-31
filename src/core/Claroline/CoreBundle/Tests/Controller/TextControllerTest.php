<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Entity\Resource\Text;

class TextControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());
        $this->client->followRedirects();
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceInstance')
            ->getWSListableRootResource($this->getFixtureReference('user/admin')->getPersonalWorkspace());
    }

    public function testAdd()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr[0]->getId());
        $this->assertEquals('This is a text', $text->name);
    }

    public function testDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr[0]->getId());
        $crawler = $this->client->request('GET', "/resource/custom/text/open/{$text->resource_id}");
        $node = $crawler->filter('#content');
        $this->assertTrue(strpos($node->text(), 'hello world') !== false);
    }

    public function testEditByRefAction()
    {
        $this->markTestSkipped('Fix me (dom crawler exception)');
        $this->logUser($this->getFixtureReference('user/admin'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr[0]->getId());
        $textId = $text->{'resourceId'};
        $crawler = $this->client->request('GET', "/text/form/edit/{$textId}");
        $form = $crawler->filter('button[type=submit]')->form();
        $crawler = $this->client->submit($form, array('content' => 'the answer is 42'));
        $crawler = $this->client->request('GET', "/resource/custom/text/open/{$textId}");
        $node = $crawler->filter('#content');
        $this->assertTrue(strpos($node->text(), 'the answer is 42')!=false);
        $textId = $text->{'resource_id'};
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
            'POST', "/resource/create/text/{$this->pwr[0]->getId()}"
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