<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResourcePropertiesControllerTest extends FunctionalTestCase
{
    private $logRepository;

    public function setUp()
    {
        parent::setUp();
        $this->loadPlatformRolesFixture();
        $this->loadUserData(array('user' => 'user', 'admin' => 'admin'));
        $this->client->followRedirects();
        $this->thumbsDir = $this->client->getContainer()->getParameter('claroline.param.thumbnails_directory');
        $this->logRepository = $this->em->getRepository('ClarolineCoreBundle:Logger\Log');
    }

    public function tearDown()
    {
        $this->cleanDirectory($this->client->getContainer()->getParameter('claroline.param.files_directory'));
        $this->cleanDirectory($this->thumbsDir);
        parent::tearDown();
    }

    public function testRenameFormCanBeDisplayed()
    {
        $this->loadDirectoryData('user', array('user/testDir'));
        $dir = $this->getDirectory('testDir');
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', "/resource/rename/form/{$dir->getId()}");
        $form = $crawler->filter('#resource_name_form');
        $this->assertEquals(count($form), 1);
    }

    public function testRenameFormErrorsAreDisplayed()
    {
        $this->loadDirectoryData('user', array('user/testDir'));
        $dir = $this->getDirectory('testDir');
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request(
            'POST', "/resource/rename/{$dir->getId()}",
            array('resource_name_form' => array('name' => ''))
        );
        $form = $crawler->filter('#resource_name_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormCanBeDisplayed()
    {
        $this->loadDirectoryData('user', array('user/testDir'));
        $dir = $this->getDirectory('testDir');
        $this->logUser($this->getUser('user'));
        $crawler = $this->client->request('GET', "/resource/properties/form/{$dir->getId()}");
        $form = $crawler->filter('#resource-properties-form');
        $this->assertEquals(count($form), 1);
    }

    public function testRename()
    {
        $now = new \DateTime();

        $this->loadDirectoryData('user', array('user/testDir'));
        $dir = $this->getDirectory('testDir');
        $this->logUser($this->getUser('user'));
        $this->client->request(
            'POST', "/resource/properties/edit/{$dir->getId()}",
            array('resource_properties_form' => array('name' => 'new_name'))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('new_name', $jsonResponse->name);

        $logs = $this->logRepository->findActionAfterDate(
            'resource_update',
            $now,
            $this->getUser('user')->getId(),
            $dir->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testChangeIcon()
    {
        $now = new \DateTime();

        $this->loadDirectoryData('user', array('user/testDir'));
        $dir = $this->getDirectory('testDir');
        $ds = DIRECTORY_SEPARATOR;
        $png = __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}icon.png";
        copy($png, __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png");

        $this->logUser($this->getUser('user'));
        $file = new UploadedFile(
            __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png",
            'image.png',
            'image/png',
            null,
            null,
            true
        );
        $this->client->request(
            'POST',
            "/resource/properties/edit/{$dir->getId()}",
            array('resource_properties_form' => array('name' => $dir->getName())),
            array('resource_properties_form' => array('userIcon' => $file))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $images = $this->getUploadedFiles($this->thumbsDir);
        $this->assertEquals(2, count($images));
        $name = str_replace("thumbnails/", "", $jsonResponse->icon);
        $this->assertContains($name, $images);

        $logs = $this->logRepository->findActionAfterDate(
            'resource_update',
            $now,
            $this->getUser('user')->getId(),
            $dir->getId()
        );
        $this->assertEquals(1, count($logs));
    }

    public function testEditShortcutIcon()
    {
        $this->loadDirectoryData('user', array('user/testDir'));
        $dir = $this->getDirectory('testDir');
        $ds = DIRECTORY_SEPARATOR;
        $png = __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}icon.png";
        copy($png, __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png");
        $pwr = $this->getDirectory('user');

        $this->logUser($this->getUser('user'));
        $this->client->request('GET', "/resource/shortcut/{$pwr->getId()}/create?ids[]={$dir->getId()}");
        $jsonResponse = json_decode($this->client->getResponse()->getContent());

        $file = new UploadedFile(
            __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png",
            'image.png',
            'image/png',
            null,
            null,
            true
        );
        $this->client->request(
            'POST', "/resource/properties/edit/{$jsonResponse[0]->id}",
            array('resource_properties_form' => array('name' => $dir->getName())),
            array('resource_properties_form' => array('userIcon' => $file))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $images = $this->getUploadedFiles($this->thumbsDir);
        $this->assertEquals(2, count($images));
        $name = $jsonResponse->icon;
        $name = substr($name, ($pos = strpos($name, 'thumbnail')) !== false ? $pos + 11 : 0);
        $this->assertContains($name, $images);

        //is it the "shortcut" icon ?
        $icon = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceIcon')
            ->findOneBy(array('relativeUrl' => $jsonResponse->icon));

        $this->assertTrue($icon->isShortcut());
    }

    private function getUploadedFiles($dir)
    {
        $iterator = new \DirectoryIterator($dir);
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
