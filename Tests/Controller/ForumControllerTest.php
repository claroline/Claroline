<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;
use Claroline\ForumBundle\DataFixtures\LoadOptionsData;

class ForumControllerTest extends FunctionalTestCase
{
    private $userWsCollaboratorRole;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user', 'ws_creator' => 'ws_creator', 'admin' => 'admin'));
        $this->userWsCollaboratorRole = $this->em
            ->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole($this->getWorkspace('user'));
        $this->client->followRedirects();
    }
/*
    public function testSubjects()
    {
        $this->loadFixture(new LoadOptionsData());
        $this->loadFixture(new LoadForumData('test', 'user', 0, 0));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client
            ->request('GET', "/forum/form/subject/{$this->getFixtureReference('forum/test')->getId()}");
        $this->assertEquals(1, count($crawler->filter('#forum_subject_form')));
        $form = $crawler->filter('button[type=submit]')->form();
        $form['forum_subject_form[title]'] = 'title';
        $form['forum_subject_form[message][content]'] = 'content';
        $this->client->submit($form);
        $crawler = $this->client->request(
            'GET',
            "/forum/{$this->getFixtureReference('forum/test')->getId()}/subjects/page"
        );
        $this->assertEquals(1, count($crawler->filter('.row-subject')));
    }
*/
    public function testMessages()
    {
        $this->loadFixture(new LoadOptionsData());
        $creator = $this->getUser('ws_creator');
        $creator->addRole($this->userWsCollaboratorRole);
        $admin = $this->getUser('admin');
        $admin->addRole($this->userWsCollaboratorRole);
        $this->em->persist($creator);
        $this->em->persist($admin);
        $this->em->flush();
        $this->loadFixture(new LoadForumData('test', 'user', 2, 2));
        $this->logUser($this->getUser('user'));
        $crawler = $this->client
            ->request('GET', "/forum/{$this->getFixtureReference('forum/test')->getId()}/subjects/page");
        $link = $crawler->filter('.link-subject')->first()->link();
        $crawler = $this->client->click($link);
        $this->assertEquals(1, count($crawler->filter('#messages_table')));
        $subjects = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($this->getFixtureReference('forum/test')->getId())
            ->getSubjects();
        $crawler = $this->client->request('GET', "/forum/subject/{$subjects[0]->getId()}/messages/page");
        $this->assertEquals(2, count($crawler->filter('.row-message')));
        $crawler = $this->client->request('GET', "/forum/add/message/{$subjects[0]->getId()}");
        $this->assertEquals(1, count($crawler->filter('#forum_message_form')));
        $form = $crawler->filter('input[type=submit]')->form();
        $form['forum_message_form[content]'] = 'content';
        $this->client->submit($form);
        $crawler = $this->client->request('GET', "/forum/subject/{$subjects[0]->getId()}/messages/page");
        $this->assertEquals(3, count($crawler->filter('.row-message')));
    }
}
