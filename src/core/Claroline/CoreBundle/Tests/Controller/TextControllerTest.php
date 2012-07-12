<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Entity\Resource\Text;

class TextManagerTest extends FunctionalTestCase
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
        $this->assertEquals('This is a text', $text->{'title'});
    }

    public function testDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr[0]->getId());
        $crawler = $this->client->request('GET', "/resource/custom/text/open/{$text->{'resourceId'}}");
        $node = $crawler->filter('#content');
        $this->assertTrue(strpos($node->text(), 'hello world') !== false);
    }

    public function testEditByRefAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $text = $this->addText('This is a text', 'hello world', $this->pwr[0]->getId());
        $textId = $text->{'resourceId'};
        $crawler = $this->client->request('GET', "/text/form/edit/{$textId}");
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('content' => 'the answer is 42'));
        $crawler = $this->client->request('GET', "/resource/custom/text/open/{$textId}");
        $node = $crawler->filter('#content');
        $this->assertTrue(strpos($node->text(), 'the answer is 42')!=false);
        $textId = $text->{'resourceId'};
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\AbstractResource')->find($textId);
        $revisions = $text->getRevisions();
        $this->assertEquals(2, count($revisions));
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