<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Resource\Event\CopyResourceEvent;
use Claroline\CoreBundle\Tests\DataFixtures\LoadFileData;

class FileControllerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    /** @var string */
    private $stubDir;

    /** @var $ResourceInstance */
    private $pwr;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user'));
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDir = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}";
        $this->upDir = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->findWorkspaceRoot($this->getFixtureReference('user/user')->getPersonalWorkspace());
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->cleanDirectory($this->upDir);
    }

    public function testUpload()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->uploadFile($this->pwr->getId(), 'text.txt');
        $this->client->request('GET', "/resource/directory/{$this->pwr->getId()}");
        $dir = json_decode($this->client->getResponse()->getContent());
        $this->assertObjectHasAttribute('resources', $dir);
        $this->assertEquals(1, count($dir->resources));
        $this->assertEquals(1, count($this->getUploadedFiles()));
    }

    public function testDelete()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $user = $this->client->getContainer()->get('security.context')->getToken()->getUser();
        $node = $this->createFile($this->pwr, 'text.txt', $user);
        $this->client->request('GET', "/resource/delete?ids[]={$node->getId()}");
        $this->client->request('POST', "/resource/directory/{$this->pwr->getId()}");
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
            "/resource/create/file/{$this->pwr->getId()}",
            array('file_form' => array()),
            array('file_form' => array('name' => null))
        );

        $form = $crawler->filter('#file_form');
        $this->assertEquals(count($form), 1);
    }

    public function testCopy()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $user = $this->client->getContainer()->get('security.context')->getToken()->getUser();
        $stdFile = $this->createFile($this->pwr, 'text.txt', $user);
        $file = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\AbstractResource')
            ->find($stdFile->getId());
        $event = new CopyResourceEvent($file);
        $this->client->getContainer()->get('event_dispatcher')->dispatch('copy_file', $event);
        $this->assertEquals(1, count($event->getCopy()));
    }

    private function uploadFile($parentId, $name)
    {
        $file = new UploadedFile(tempnam(sys_get_temp_dir(), 'FormTest'), $name, 'text/plain', null, null, true);
        $this->client->request(
            'POST',
            "/resource/create/file/{$parentId}",
            array('file_form' => array()),
            array('file_form' => array('file' => $file, 'name' => 'name'))
        );

        $obj = json_decode($this->client->getResponse()->getContent());

        return $obj[0];
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
