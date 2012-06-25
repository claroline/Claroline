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
    }

    public function testAdd()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $ri = $this->addText('Hello world', $this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId());
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($ri->getId())->getResource();
        $this->assertEquals('Hello world', $text->getLastRevision()->getContent());
        $this->assertEquals(1, count($text->getRevisions()));
        $this->assertEquals(1, count($text->getLastRevision()));
    }

    public function testDefaultAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $ri = $this->addText('Hello world', $this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId());
        $crawler = $this->client->request('GET', "/resource/click/{$ri->getId()}");
        $node = $crawler->filter('#content');

        $this->assertTrue(strpos($node->text(), 'Hello world') !== false);
    }

    public function testEditByRefAction()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $ri = $this->addText('Hello world', $this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId());
        $crawler = $this->client->request('GET', "/resource/edit/{$ri->getId()}/{$this->getFixtureReference('user/admin')->getPersonnalWorkspace()->getId()}/ref");
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('content' => 'the answer is 42'));
        $crawler = $this->client->request('GET', "/resource/click/{$ri->getId()}");
        $node = $crawler->filter('#content');
        $this->assertTrue(strpos($node->text(), 'the answer is 42')!=false);
        $text = $this->em->getRepository('ClarolineCoreBundle:Resource\ResourceInstance')->find($ri->getId())->getResource();
        $revisions = $text->getRevisions();
        $this->assertEquals(2, count($revisions));
    }

    private function addText($txt, $workspaceId, $parentId = null)
    {
        $text = new Text();
        $text->setText($txt);

        return $this->addResource($text, $workspaceId);
    }

    private function addResource($object, $workspaceId, $parentId = null)
    {
        return $ri = $this
            ->client
            ->getContainer()
            ->get('claroline.resource.creator')
            ->createResource(
                $parentId,
                $workspaceId,
                $object,
                true
                );
    }
}