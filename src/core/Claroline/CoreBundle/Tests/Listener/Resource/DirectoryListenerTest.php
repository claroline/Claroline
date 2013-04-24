<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;
use DirectoryIterator;

class DirectoryListenerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
        $this->upDir = $this->client->getContainer()->getParameter('claroline.param.files_directory');
        $this->cleanDirectory($this->upDir);
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUserCanCreateDirectory()
    {
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'POST',
            "/resource/create/directory/{$this->getDirectory('user')->getId()}",
            array('directory_form' => array()),
            array('directory_form' => array('name' => 'name'))
        );
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
    }

    public function testUserCanRemoveDirectoryAndItsContent()
    {
        $this->loadDirectoryData('user', array('user/dir1/dir2'));
        $this->loadFileData('user', 'dir2', array('foo.txt'));
        $dirRi = $this->getDirectory('dir1');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/delete?ids[]={$dirRi->getId()}");
        $this->client->request('GET', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(0, count($dir->resources));
    }

    public function testExportTemplate()
    {
        $this->loadDirectoryData('user', array('user/dir1/dir2/dir3'));
        $event = new ExportResourceTemplateEvent($this->getDirectory('dir1'));
        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_directory_to_template', $event);
        $config = $event->getConfig();
        $this->assertEquals(1, count($config['children']));
        $this->assertEquals(1, count($config['children'][0]['children']));
    }

    public function testImportTemplate()
    {
        $canDo = array(
            'canEdit' => false,
            'canOpen' => true,
            'canDelete' => false,
            'canCopy' => false,
            'canExport' => false,
            'canCreate' => array('directory')
        );

        $perms = array(
            'ROLE_WS_VISITOR' => $canDo,
            'ROLE_WS_COLLABORATOR' => $canDo,
            'ROLE_WS_MANAGER' => $canDo
        );

        $directory = array(
            'type' => 'directory',
            'name' => 'dir1',
            'id' => 1,
            'children' => array(array(
                'type' => 'directory',
                'name' => 'dir2',
                'id' => 2,
                'children' => array(),
                'perms' => $perms
            )),
            'perms' => $perms
        );

        $event = new ImportResourceTemplateEvent(
            $directory,
            $this->getDirectory('user'),
            $this->getUser('user')
        );

        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_directory_from_template', $event);
        $this->assertEquals(2, count($event->getCreatedResources()));
    }

    private function cleanDirectory($dir)
    {
        $iterator = new DirectoryIterator($dir);

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() !== 'placeholder'
                && $file->getFilename() !== 'originalFile.txt'
                && $file->getFilename() !== 'originalZip.zip'
            ) {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }
}
