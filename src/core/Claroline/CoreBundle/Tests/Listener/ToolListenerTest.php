<?php

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Event\ExportToolEvent;
use Claroline\CoreBundle\Listener\Tool\HomeListener;
use Claroline\CoreBundle\Listener\Tool\ResourceManagerListener;

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
        $listener = new HomeListener(
            $this->client->getContainer()->get('doctrine.orm.entity_manager'),
            $this->client->getContainer()->get('event_dispatcher'),
            $this->client->getContainer()->get('templating'),
            $this->client->getContainer()->get('claroline.widget.manager')
        );
        $event = new ExportToolEvent($this->getWorkspace('user'));
        $listener->onExportHome($event);
        $config = $event->getConfig();
        //resource logger should be the 1st on the list
        $this->assertEquals(false, $config['widget'][0]['is_visible']);
    }

    public function testOnExportResources()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $this->loadFileData('user', 'user', array('foo.txt', 'bar.txt'));
        $this->loadDirectoryData('user', array('user/container'));
        $event = new ExportToolEvent($this->getWorkspace('user'));
        $listener = new ResourceManagerListener(
            $this->client->getContainer()->get('doctrine.orm.entity_manager'),
            $this->client->getContainer()->get('event_dispatcher'),
            $this->client->getContainer()->get('templating'),
            $this->client->getContainer()->get('claroline.resource.manager')
        );
        $listener->onExportResource($event);
        $config = $event->getConfig();
        $this->assertEquals(2, count($config['resources']));
        $this->assertEquals(1, count($config['directory']));
        $this->assertEquals(2, count($event->getFiles()));
    }
}

