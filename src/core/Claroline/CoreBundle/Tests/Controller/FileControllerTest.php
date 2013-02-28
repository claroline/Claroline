<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Event\CopyResourceEvent;
use Claroline\CoreBundle\Tests\DataFixtures\LoadFileData;

class FileControllerTest extends FunctionalTestCase
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
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUpload()
    {
        $this->logUser($this->getFixtureReference('user/user'));
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
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', "/resource/delete?ids[]={$node->getId()}");
        $this->client->request('POST', "/resource/directory/{$this->getDirectory('user')->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(0, count($dir->resources));
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }

    public function testCreationFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', 'resource/form/file');
        $form = $crawler->filter('#file_form');
        $this->assertEquals(count($form), 1);
    }

    public function testFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
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
        $this->logUser($this->getFixtureReference('user/user'));
        $file = $this->getFile('foo.txt');
        $event = new CopyResourceEvent($file);
        $this->client->getContainer()->get('event_dispatcher')->dispatch('copy_file', $event);
        $this->assertEquals(1, count($event->getCopy()));
    }

    private function createFile($parent, $name, User $user)
    {
        $fileData = new LoadFileData($name, $parent, $user, tempnam(sys_get_temp_dir(), 'FormTest'));
        $this->loadFixture($fileData);

        return $fileData->getLastFileCreated();
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
