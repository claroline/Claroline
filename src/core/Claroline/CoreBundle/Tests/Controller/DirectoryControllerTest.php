<?php

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Tests\DataFixtures\LoadDirectoryData;
use Claroline\CoreBundle\Tests\DataFixtures\Additional\LoadFileData;
use Claroline\CoreBundle\DataFixtures\LoadMimeTypeData;

class DirectoryControllerTest extends FunctionalTestCase
{
   /** @var string */
    private $upDir;

    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->loadFixture(new LoadResourceTypeData());     
        $this->client->followRedirects();
        $this->upDir  = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
    }

    public function tearDown()
    {
       parent::tearDown();
       $this->cleanDirectory($this->upDir);
    }

    public function testUserCanCreateRootDirectory()
    {
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/resource/directory');
        $form = $crawler->filter('input[type=submit]')->form();
        $form['select_resource_form[type]'] = $this->getFixtureReference('resource_type/directory')->getId();
        $crawler = $this->client->submit($form);
        $form = $crawler->filter('input[type=submit]')->form();
        $this->client->submit($form, array('directory_form[name]' => 'abc'));
        $crawler = $this->client->request('GET', '/resource/directory');
        $this->assertEquals(1, count($crawler->filter(".row_resource")));
    }

    public function testUserCanCreateSubDirectory()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->createRootDirectory('DIR_ROOT_user');
        $crawler = $this->client->request('GET', "/resource/click/{$id}");
        $form = $crawler->filter('input[type=submit]')->form();
        $dirTypeId = $this->getFixtureReference('resource_type/directory')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $dirTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $this->client->submit($form, array('directory_form[name]' => 'abc'));
        $crawler = $this->client->request('GET', "/resource/click/{$id}");
        $this->assertEquals(1, count($crawler->filter(".row_resource")));
    }

    public function testUserCanCreateSubResource()
    {
        $ds = DIRECTORY_SEPARATOR;
        $filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->createRootDirectory('DIR_ROOT_user');
        $crawler = $this->client->request('GET', "/resource/click/{$id}");
        $form = $crawler->filter('input[type=submit]')->form();
        $form['select_resource_form[type]'] = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form);
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('file_form[name]' => $filePath));
        $crawler = $this->client->request('GET', "/resource/click/{$id}");
        $this->assertEquals(1, count($crawler->filter(".row_resource")));
    }

    public function testUserCanRemoveDirectoryAndItsContent()
    {
        $ds = DIRECTORY_SEPARATOR;
        $filePath = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}originalFile.txt";
        $this->logUser($this->getFixtureReference('user/user'));
        $id = $this->createRootDirectory('DIR_ROOT_user');
        $crawler = $this->client->request('GET', "/resource/click/{$id}");
        $form = $crawler->filter('input[type=submit]')->form();
        $form['select_resource_form[type]'] = $this->getFixtureReference('resource_type/file')->getId();
        $crawler = $this->client->submit($form);
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('file_form[name]' => $filePath));
        $link = $crawler->filter("#link_resource_delete_{$id}")->link();
        $this->client->click($link);
        $crawler = $this->client->request('GET', '/resource/directory');
        $this->assertEquals(0, count($this->getUploadedFiles($this->upDir)));
        $this->assertEquals(0, count($crawler->filter(".row_resource")));
    }

    private function cleanDirectory($dir)
    {
        $iterator = new \DirectoryIterator($dir);

        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder'
                    && $file->getFilename() !== 'originalFile.txt'
                    && $file->getFilename() !== 'originalZip.zip'
               )
            {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }

     private function getUploadedFiles()
     {
        $iterator = new \DirectoryIterator($this->upDir);
        $uploadedFiles = array();

        foreach($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder')
            {
                $uploadedFiles[] = $file->getFilename();
            }
        }

        return $uploadedFiles;
     }

     private function createRootDirectory($name)
     {
        $crawler = $this->client->request('GET', '/resource/directory/null');
        $form = $crawler->filter('input[type=submit]')->form();
        $fileTypeId = $this->getFixtureReference('resource_type/directory')->getId();
        $crawler = $this->client->submit($form, array('select_resource_form[type]' => $fileTypeId));
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('directory_form[name]' => $name));
        $id = $crawler->filter(".row_resource")->last()->attr('data-resource_id');

        return $id;
     }
}
