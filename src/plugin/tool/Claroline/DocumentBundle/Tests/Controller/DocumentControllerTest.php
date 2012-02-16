<?php

namespace Claroline\DocumentBundle\Tests\Controller;

use Claroline\CoreBundle\Testing\FixtureTestCase;
use Claroline\DocumentBundle\Tests\DataFixtures\LoadDirectoryData;
use Claroline\DocumentBundle\Tests\DataFixtures\LoadDocumentData;

class DocumentControllerTest extends FixtureTestCase
{
    /** @var string */
    private $uploadDirectory;

    /** @var string */
    private $stubDirectory;

    /** @var Directory */
    private $root;

    protected function setUp()
    {
        parent::setUp();
        $this->uploadDirectory = $this->client
            ->getContainer()
            ->getParameter('claroline.files.directory');
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDirectory = __DIR__ . "{$ds}..{$ds}stubs{$ds}";
        $this->cleanUploadDirectory();
        $this->loadFixture(new LoadDocumentData());
        $this->root = $this->getFixtureReference("dir/dir_a");

        $this->client->followRedirects();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cleanUploadDirectory();
    }

    public function testUploadedFilesAppearInTheDocumentList()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->root->getId());
        $this->uploadFile("{$this->stubDirectory}otherMoveTest.txt", $this->root->getId());
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $this->assertEquals(2, $crawler->filter('.document_item')->count());
        $this->assertEquals(2, count($this->getUploadedFiles()));
    }

    public function testAddDirAppearInTheDirList()
    {
        $this->addDirectory("DIR_TEST", $this->root->getId());
        $crawler = $this->client->request('GET', "document/show/directory/" . $this->root->getId());
        $this->assertEquals(3, $crawler->filter('.directory_item')->count());
    }

    public function testUploadedFileCanBeDownloaded()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->root->getId());
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $link = $crawler->filter('.link_download_document')->eq(0)->link();
        $crawler = $this->client->click($link);
        $this->assertTrue($this->client->getResponse()->headers->contains(
            'Content-Disposition', 'attachment; filename=moveTest.txt'));
    }

    public function testUploadedFileCanBeDeleted()
    {
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->root->getId());
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $link = $crawler->filter('.link_delete_document')->eq(0)->link();
        $this->client->click($link);
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $this->assertEquals(0, $crawler->filter('.document_item')->count());
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }

    public function testDirCanBeRemoved()
    {
        $this->addDirectory("DIR_TEST", $this->root->getId());
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $link = $crawler->filter('.link_directory_show')->eq(0)->link();
        $this->client->click($link);
        $this->uploadFile("{$this->stubDirectory}moveTest.txt", $this->client->getRequest()->get('id'));
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $link = $crawler->filter('.link_delete_directory')->eq(0)->link();
        $this->client->click($link);
        $crawler = $this->client->request('GET', 'document/show/directory/' . $this->root->getId());
        $this->assertEquals(0, $crawler->filter('.directory_item')->count());
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }

    private function cleanUploadDirectory()
    {
        $iterator = new \DirectoryIterator($this->uploadDirectory);

        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder')
            {
                chmod($file->getPathname(), 0777);
                unlink($file->getPathname());
            }
        }
    }

    private function getUploadedFiles()
    {
        $iterator = new \DirectoryIterator($this->uploadDirectory);
        $uploadedFiles = array();

        foreach ($iterator as $file)
        {
            if ($file->isFile() && $file->getFilename() !== 'placeholder')
            {
                $uploadedFiles[] = $file->getFilename();
            }
        }

        return $uploadedFiles;
    }

    private function uploadFile($filePath, $id)
    {
        $this->client->restart();
        $crawler = $this->client->request('GET', "document/show/directory/" . $id);
        $form = $crawler->filter('input[type=submit]')->first()->form();
        $this->client->submit($form, array('Document_Form[file]' => $filePath));
        $this->client->restart();
    }

    private function addDirectory($name, $id)
    {
        $this->client->restart();
        $crawler = $this->client->request('GET', "document/show/directory/" . $id);
        $form = $crawler->filter('input[type=submit]')->last()->form();
        $crawler = $this->client->submit($form, array('Directory_Form[name]' => $name));
        $this->client->restart();
    }

}
