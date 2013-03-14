<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Event\ExportWorkspaceEvent;

class ToolListenerTest extends FunctionalTestCase
{
    public function testOnExportHome()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $loggerWidget = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\Widget')
            ->findOneByName('core_resource_logger');
        //set the resource logger as invisible
        $displayConfig = $this->em
            ->getRepository('ClarolineCoreBundle:Widget\DisplayConfig')
            ->findOneBy(array('workspace' => $this->getWorkspace('user'), 'widget' => $loggerWidget));
        $displayConfig->invertVisible();
        $listener = new ToolListener();
        $listener->setContainer($this->client->getContainer());
        $event = new ExportWorkspaceEvent($this->getWorkspace('user'), new \ZipArchive());
        $listener->onExportHome($event);
        $config = $event->getConfig();
        //resource logger should be the 1st on the list
        $this->assertEquals(false, $config['widget'][0]['is_visible']);
    }

    public function testOnExportResources()
    {
        $this->markTestSkipped('I think that Zips are a source of problem for this test');
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $archive = new \ZipArchive();
        $archive->open(
            $this->client->getContainer()->getParameter('claroline.workspace_template.directory').'demo.zip',
            \ZipArchive::CREATE
        );
        $event = new ExportWorkspaceEvent($this->getWorkspace('user'), new \ZipArchive());
        $listener = new ToolListener();
        $listener->setContainer($this->client->getContainer());
        $listener->onExportResource($event);
        $config = $event->getConfig();
        $archive->close();
        $this->assertEquals(2, count($config['resources']));
        $this->assertEquals(1, count($config['resources'][0]['children']));
    }
}

