<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class TextControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
    }

    public function testEditByRefAction()
    {
        $this->logUser($this->getUser('user'));
        $text = $this->addText('This is a text', 'hello world', $this->getDirectory('user')->getId());
        $crawler = $this->client->request('GET', "/text/form/edit/{$text->id}");
        var_dump($this->client->getResponse()->getContent());
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