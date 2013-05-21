<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class CalendarControllerTest extends FunctionalTestCase
{
    private $logRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(array('ws_a' => 'ws_creator'));
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function testWorkspaceUserCanSeeTheAgenda()
    {
        $now = new \DateTime();

        $workspaceId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $this->client->request('GET', "/workspaces/{$workspaceId}/open/tool/calendar");
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);

        $logs = $this->logRepository->findActionAfterDate(
            'ws_tool_read',
            $now,
            $this->getUser('ws_creator')->getId(),
            null,
            $workspaceId,
            null,
            null,
            null,
            'calendar'
        );
        $this->assertEquals(1, count($logs));
    }

    public function testShowWorkspaceCalendar()
    {
        $workspaceId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/add",
            array(
                'calendar_form' => array(
                    'title' => 'foo',
                    'end' => '22-02-2013',
                    'description' => 'ghhkkgf',
                    'allDay' => true,
                    'priority' => '#01A9DB'
                ),
                'date' => 'Thu Jan 24 2013 00:00:00 GMT+0100'
            )
        );
        $status = $this->client->request('GET', "/workspaces/tool/calendar/{$workspaceId}/show");
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);
    }

    public function testAddEventCalendar()
    {
        $workspaceId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/add",
            array(
                'calendar_form' => array(
                    'title' => 'foo',
                    'end' => '22-02-2013',
                    'description' => 'ghhkkgf',
                    'allDay' => true,
                    'priority' => '#01A9DB'
                ),
                'date' => 'Thu Jan 24 2013 00:00:00 GMT+0100'
            )
        );

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);
    }

    public function testDeleteEventCalendar()
    {
        $workspaceId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/add",
            array(
                'calendar_form' => array(
                    'title' => 'foo',
                    'description' => 'ghhkkgf',
                    'end' => '22-02-2013',
                    'allDay' => true
                ),
                'date' => 'Thu Jan 24 2013 00:00:00 GMT+0100'
            )
        );

        $data = $this->client->getResponse()->getContent();
        $data = json_decode($data, true);
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/delete",
            array(
                'id' => $data['id']
            )
        );

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);

    }

    public function testMoveEventCalendar()
    {
        $workspaceId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/add",
            array(
                'calendar_form' => array(
                    'title' => 'foo',
                    'description' => 'ghhkkgf',
                    'end' => '22-02-2013',
                    'allDay' => true
                ),
                'date' => 'Thu Jan 24 2013 00:00:00 GMT+0100'
            )
        );
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $dataForm = array(
            'id' => $content['id'],
            'dayDelta' => '1',
            'minuteDelta' => '0'
        );
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/move",
            $dataForm
        );

        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);

    }

    public function testUpdateEvent()
    {
        $workspaceId = $this->getWorkspace('ws_a')->getId();
        $this->logUser($this->getUser('ws_creator'));
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/add",
            array(
                'calendar_form' => array(
                    'title' => 'foo',
                    'description' => 'ghhkkgf',
                    'end' => '22-02-2013',
                    'allDay' => true,
                    'priority' => '#01A9DB'
                   ),
                  'date' => 'Thu Jan 24 2013 00:00:00 GMT+0100'
                )
        );
        $status = $this->client->getResponse()->getStatusCode();
        $this-> assertEquals(200, $status);
        $content = json_decode($this->client->getResponse()->getContent(), true);
        $this->client->request(
            'POST',
            "/workspaces/tool/calendar/{$workspaceId}/update",
            array(
                'calendar_form' => array(
                    'title' => 'foo',
                    'description' => 'ghhkkgf',
                    'end' => '22-02-2013',
                    'allDay' => true,
                    'priority' => '#01A9DB'
                ),
                'id' => $content['id']
            )
        );
        $status = $this->client->getResponse()->getStatusCode();
        $this->assertEquals(200, $status);
    }
}
