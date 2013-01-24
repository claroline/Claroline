<?php

namespace Claroline\CoreBundle\Controller\ResourceController;

use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResourcePropertiesControllerTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('user', 'admin'));
        $this->client->followRedirects();
        $this->pwr = $this
            ->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\AbstractResource')
            ->getRootForWorkspace($this->getFixtureReference('user/user')->getPersonalWorkspace());
        $this->thumbsDir = $this->client->getContainer()->getParameter('claroline.thumbnails.directory');
    }

    public function tearDown()
    {
        $this->cleanDirectory($this->client->getContainer()->getParameter('claroline.files.directory'));
        $this->cleanDirectory($this->thumbsDir);
        parent::tearDown();
    }

    public function testRenameFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr, 'testDir');
        $crawler = $this->client->request('GET', "/resource/rename/form/{$dir->getId()}");
        $form = $crawler->filter('#resource_name_form');
        $this->assertEquals(count($form), 1);
    }

    public function testRenameFormErrorsAreDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr, 'testDir');
        $crawler = $this->client->request(
            'POST', "/resource/rename/{$dir->getId()}",
            array('resource_name_form' => array('name' => ''))
        );
        $form = $crawler->filter('#resource_name_form');
        $this->assertEquals(count($form), 1);
    }

    public function testPropertiesFormCanBeDisplayed()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr, 'testDir');
        $crawler = $this->client->request('GET', "/resource/properties/form/{$dir->getId()}");
        $form = $crawler->filter('#resource-properties-form');
        $this->assertEquals(count($form), 1);
    }

    public function testRename()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr, 'testDir');
        $this->client->request(
            'POST', "/resource/properties/edit/{$dir->getId()}",
            array('resource_properties_form' => array('name' => 'new_name'))
        );
        $jsonResponse = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals('new_name', $jsonResponse->name);
    }

    public function testChangeIcon()
    {
        $ds = DIRECTORY_SEPARATOR;
        $png = __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}icon.png";
        copy($png, __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png");

        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr, 'testDir');
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
        $name = str_replace("thumbnails{$ds}", "", $jsonResponse->icon);
        $this->assertContains($name, $images);
    }

    public function testEditShortcutIcon()
    {
        $ds = DIRECTORY_SEPARATOR;
        $png = __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}icon.png";
        copy($png, __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}iconcopy.png");

        $this->logUser($this->getFixtureReference('user/user'));
        $dir = $this->createDirectory($this->pwr, 'testDir');
        $this->client->request('GET', "/resource/shortcut/{$this->pwr->getId()}/create?ids[]={$dir->getId()}");
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
        $name = str_replace("thumbnails{$ds}", "", $jsonResponse->icon);
        $this->assertContains($name, $images);

        //is it the "shortcut" icon ?
        $icon = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->findOneBy(array('relativeUrl' => $jsonResponse->icon));

        $this->assertTrue($icon->isShortcut());
    }

    private function createDirectory($parent, $name)
    {
        $user = $this->client->getContainer()->get('security.context')->getToken()->getUser();
        $manager = $this->client->getContainer()->get('claroline.resource.manager');
        $directory = new Directory();
        $directory->setName($name);

        return $manager->create($directory, $parent->getId(), 'directory', $user);
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
