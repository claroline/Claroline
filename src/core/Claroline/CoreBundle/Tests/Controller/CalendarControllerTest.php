<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadUserData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadWorkspaceData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadPlatformRolesData; // to be removed...

class CalendarControllerTest extends FunctionalTestCase
{

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadPlatformRolesData); // to be removed...

        $this->loadFixture(new LoadUserData);
        $this->loadFixture(new LoadWorkspaceData);
    }

    public function testWorkspaceUserCanSeeTheAgenda()
    {

        $workspaceId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request('GET', "/workspaces/{$workspaceId}/agenda");
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);
    }

    public function testAddEventCalendar()
    {
        $workspaceId = $this->getFixtureReference('workspace/ws_a')->getId();
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $this->client->request(
            'POST', 
            "/workspaces/{$workspaceId}/agenda/add",
            array(
                'title' => 'foo',
                'start' => '2013-12-01',
                'end' => array(
                    'day'=> '01',
                    'month'=> '12',
                    'year'=> '2013',
                )
            )
         );
        $status = $this->client->getResponse()->getStatusCode();
        
        var_dump($this->client->getResponse()->getContent());
        
        
        $this->assertEquals(200, $status);
    }

}