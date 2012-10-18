<?php

namespace Claroline\ForumBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\ForumBundle\Tests\DataFixtures\LoadForumData;

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
/*
    public function testIndex(){
        $this->logUser($this->getFixtureReference('user/user'));
        $this->loadFixture(new LoadForumData());
        $this->client->request('GET', "/forum/open/{$this->getFixtureReference('forum/forumInstance')->getId()}");
        var_dump($this->client->getResponse()->getContent());
    }

    public function testSubjects(){

    }

    public function testSubjectCreation(){

    }

    public function testMessages(){

    }

    public function testMessageCreation()
    {

    }*/
}
