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
        $event = new ExportWorkspaceEvent($this->getWorkspace('user'));
        $listener->onExportHome($event);
        $config = $event->getConfig();
        //resource logger should be the 1st on the list
        $this->assertEquals(false, $config['widget'][0]['is_visible']);
    }

    public function testOnExportResources()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $this->loadDirectoryData('user', array('user/dir2/dir3'));
        $this->loadFileData('user', 'user', array('file.txt'));
        $event = new ExportWorkspaceEvent($this->getWorkspace('user'));
        $listener = new ToolListener();
        $listener->setContainer($this->client->getContainer());
        $listener->onExportResource($event);
        $config = $event->getConfig();
        $this->assertEquals(2, count($config['resources']));
        $this->assertEquals(1, count($config['resources'][0]['children']));
    }
}

