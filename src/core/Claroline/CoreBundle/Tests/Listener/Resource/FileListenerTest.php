<?php

namespace Claroline\CoreBundle\Listener\Resource;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Library\Event\ExportResourceTemplateEvent;
use Claroline\CoreBundle\Library\Event\ImportResourceTemplateEvent;

class FileListenerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    /** @var string */
    private $stubDir;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user'));
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDir = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}";
        $this->upDir = $this->client->getContainer()->getParameter('claroline.param.files_directory');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUpload()
    {
        $this->logUser($this->getUser('user'));
        $file = new UploadedFile(tempnam(sys_get_temp_dir(), 'FormTest'), 'file.txt', 'text/plain', null, null, true);
        $this->client->request(
            'POST',
            "/resource/create/file/{$this->getDirectory('user')->getId()}",
            array('file_form' => array()),
            array('file_form' => array('file' => $file, 'name' => 'name'))
        );

        $this->client->request('GET', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
        $this->assertEquals(1, count($this->getUploadedFiles()));
    }

    public function testDelete()
    {
        $this->loadFileData('user', 'user', array('foo.txt'));
        $node = $this->getFile('foo.txt');
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/delete?ids[]={$node->getId()}");
        $this->client->request('POST', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(0, count($dir->resources));
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }

    public function testCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', 'resource/form/file');
        $form = $crawler->filter('#file_form');
        $this->assertEquals(count($form), 1);
    }

    public function testFormErrorsAreDisplayed()
    {
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'POST',
            "/resource/create/file/{$this->getDirectory('user')->getId()}",
            array('file_form' => array()),
            array('file_form' => array('name' => null))
        );

        $form = $crawler->filter('#file_form');
        $this->assertEquals(count($form), 1);
    }

    public function testCopy()
    {
        $this->loadFileData('user', 'user', array('foo.txt'));
        $this->logUser($this->getUser('user'));
        $file = $this->getFile('foo.txt');
        $event = new CopyResourceEvent($file);
        $this->client->getContainer()->get('event_dispatcher')->dispatch('copy_file', $event);
        $this->assertEquals(1, count($event->getCopy()));
    }

    public function testExportTemplate()
    {
        $this->loadFileData('user', 'user', array('foo.txt'));
        $event = new ExportResourceTemplateEvent($this->getFile('foo.txt'));
        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_file_to_template', $event);
        $this->assertEquals(1, count($event->getFiles()));
        $this->assertEquals(1, count($event->getConfig()));
    }

    public function testImportTemplate()
    {
        $resource = array();
        $files = array(tempnam(sys_get_temp_dir(), 'claro_tmp_'));
        $event = new ImportResourceTemplateEvent(
            $resource,
            $this->getDirectory('user'),
            $this->getUser('user')
        );
        $event->setFiles($files);
        $this->client->getContainer()->get('event_dispatcher')->dispatch('resource_file_from_template', $event);
        $this->assertEquals(1, count($event->getResource()));
        $this->assertEquals(1, count($this->getUploadedFiles()));
    }

    private function getUploadedFiles()
    {
        $iterator = new \DirectoryIterator($this->upDir);
        $uploadedFiles = array();

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() !== 'placeholder') {
                $uploadedFiles[] = $file->getFilename();
            }
        }

        return $uploadedFiles;
    }

    private function cleanDirectory($dir)
    {
        $iterator = new \DirectoryIterator($dir);

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
