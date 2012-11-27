<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;
use Claroline\ForumBundle\DataFixtures\LoadOptionsData;
use Claroline\CoreBundle\Tests\DataFixtures\Special\LoadEntitiesInWorkspace;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUsersData;

class ForumControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testSubjects()
    {
        $this->loadFixture(new LoadOptionsData());
        $this->loadFixture(new LoadForumData('test', 'user', 0, 0));
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/forum/form/subject/{$this->getFixtureReference('forum_instance/forum')->getId()}");
        $this->assertEquals(1, count($crawler->filter('#subject_forum_form')));
        $form = $crawler->filter('button[type=submit]')->form();
        $form['subject_forum_form[title]'] = 'title';
        $form['subject_forum_form[message][content]'] = 'content';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/forum/{$this->getFixtureReference('forum_instance/forum')->getId()}/offset/0");
        $this->assertEquals(1, count($crawler->filter('.row-subject')));
    }

    public function testMessages()
    {
        $this->loadFixture(new LoadOptionsData());
        $fix = new LoadEntitiesInWorkspace(2, 'user', 'user');
//        $fix->setLogger(function($log){echo $log."\n";});
        $this->loadFixture($fix);
        $ffix = new LoadForumData('test', 'user', 2, 2);
//        $ffix->setLogger(function($log){echo $log."\n";});
        $this->loadFixture($ffix);

        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', "/forum/{$this->getFixtureReference('forum_instance/forum')->getId()}/offset/0");
        $link = $crawler->filter('.link-subject')->first()->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, count($crawler->filter('#messages_table')));
        $subjects = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($this->getFixtureReference('forum_instance/forum')->getId())
            ->getChildren();

        $crawler = $this->client->request('GET', "/forum/subject/{$subjects[0]->getId()}/offset/0");
        $this->assertEquals(2, count($crawler->filter('.row-message')));
        $crawler = $this->client->request('GET', "/forum/add/message/{$subjects[0]->getId()}");
        $this->assertEquals(1, count($crawler->filter('#message_forum_form')));
        $form = $crawler->filter('input[type=submit]')->form();
        $form['message_forum_form[content]'] = 'content';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/forum/subject/{$subjects[0]->getId()}/offset/0");
        $this->assertEquals(3, count($crawler->filter('.row-message')));

    }
}
