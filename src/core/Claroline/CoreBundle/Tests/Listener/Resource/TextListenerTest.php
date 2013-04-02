<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\DeleteResourceEvent;

class TextListenerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
    }

    public function testAdd()
    {
        $this->logUser($this->getUser('user'));
        $text = $this->addText('This is a text', 'hello world', $this->getDirectory('user')->getId());
        $this->assertEquals('This is a text', $text->name);
    }

    public function testDefaultAction()
    {
        $this->logUser($this->getUser('user'));
        $text = $this->addText('This is a text', 'hello world', $this->getDirectory('user')->getId());
        $crawler = $this->client->request('GET', "/resource/open/text/{$text->id}");
        $node = $crawler->filter('#text_content');
        $this->assertTrue(strpos($node->text(), 'hello world') !== false);
    }

    public function testCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', 'resource/form/text');
        $form = $crawler->filter('#text_form');
        $this->assertEquals(count($form), 1);
    }

    public function testFormErrorsAreDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'POST', "/resource/create/text/{$this->getDirectory('user')->getId()}"
        );

        $form = $crawler->filter('#text_form');
        $this->assertEquals(count($form), 1);
    }

    public function testOnCopyText()
    {
        $this->logUser($this->getUser('user'));
        $text = $this->addText('This is a text', 'hello world', $this->getDirectory('user')->getId());
        $this->client->request(
            'GET',
            "/resource/copy/{$this->getDirectory('user')->getId()}?ids[]={$text->id}"
        );
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(2, count($dir->resources));
    }

    public function testExportTemplate()
    {
        $this->loadTextData('user', 'user', 200, array('foo'));

        $event = new ExportResourceTemplateEvent($this->getText('foo'));
        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_text_to_template', $event);
        $config = $event->getConfig();
        $this->assertTrue(isset($config['text']));
    }

    public function testImportTemplate()
    {
        $text['text'] = 'Hello world !';

        $event = new ImportResourceTemplateEvent(
            $text,
            $this->getDirectory('user'),
            $this->getUser('user')
        );

        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_text_from_template', $event);
        $this->assertEquals(1, count($event->getResource()->getRevisions()));
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