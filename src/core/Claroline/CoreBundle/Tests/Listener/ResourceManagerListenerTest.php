<?php

namespace Claroline\CoreBundle\Listener\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Listener\Tool\ResourceManagerListener;
use Claroline\CoreBundle\Library\Event\ImportToolEvent;
use Symfony\Component\Yaml\Yaml;

class ResourceManagerListenerTest extends FunctionalTestCase
{
    public function testOnImportResources()
    {
        $this->loadPlatformRoleData();
        $this->loadUserData(array('user' => 'user'));
        $listener = new ResourceManagerListener(
            $this->client->getContainer()->get('doctrine.orm.entity_manager'),
            $this->client->getContainer()->get('event_dispatcher'),
            $this->client->getContainer()->get('templating'),
            $this->client->getContainer()->get('claroline.resource.manager')
        );

        $root = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findOneBy(array('parent' => null, 'workspace' => $this->getWorkspace('user')));

        $zipFile = $this->client->getContainer()
            ->getParameter('claroline.param.templates_directory').'complex.zip';
        $archive = new \ZipArchive();
        $archive->open($zipFile);
        $parsedFile = Yaml::parse($archive->getFromName('config.yml'));
        $extractPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('claro_ws_tmp_', true);
        $archive->extractTo($extractPath);
        $config = $parsedFile['tools']['resource_manager'];
        $realPaths = array();

        foreach ($config['files'] as $path) {
            $realPaths[] = $extractPath . DIRECTORY_SEPARATOR . $path;
        }

        $event = new ImportToolEvent(
            $this->getWorkspace('user'),
            $config,
            $root,
            $this->getUser('user')
        );

        $event->setFiles($realPaths);
        $listener->onImportResource($event);

        $files = $this->client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\File')
            ->findAll();

        $this->assertEquals(2, count($files));
    }
}
