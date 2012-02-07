<?php

namespace Claroline\DocumentBundle\Tests\Controller;

use Claroline\CoreBundle\Testing\TransactionalTestCase;

class DocumentControllerTest extends TransactionalTestCase
{
    private $dir;

    public function __construct()
    {
        //path = __DIR__/../../../../../../../files
        //path = __DIR__/../../../../../../../test/files   
        parent::__construct();
        $ds = DIRECTORY_SEPARATOR;
        $this->dir = __DIR__ . "{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}..{$ds}test{$ds}files";
    }

    public function setUp()
    {
        parent::setUp();
        $dirArray = $this->parseDirectory($this->dir);
        $this->cleanDirectory($dirArray, $this->dir);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $dirArray = $this->parseDirectory($this->dir);
        $this->cleanDirectory($dirArray, $this->dir);
    }

    public function testUploadThenDownloadThenDeleteDocument()
    {
        $ds = DIRECTORY_SEPARATOR;
        $file = __DIR__ . $ds . ".." . $ds . "stubs" . $ds . "moveTest.txt";
        //add file number 1 
        $crawler = $this->client->request('GET', '/document/form');
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('Document_Form[file]' => $file));
        //add file number 2
        $file = __DIR__ . $ds . ".." . $ds . "stubs" . $ds . "otherMoveTest.txt";
        $crawler = $this->client->request('GET', '/document/form');
        $form = $crawler->filter('input[type=submit]')->form();
        $crawler = $this->client->submit($form, array('Document_Form[file]' => $file));
        //test
        $crawler = $this->client->request('GET', '/document/list');
        $this->assertEquals(2, $crawler->filter('#document_item')->count());
        $this->assertEquals(3, count($this->parseDirectory($this->dir)));
        //download file number 1
        $crawler = $this->client->request('GET', '/document/list');
        $link = $crawler->filter('#link_download')->eq(0)->link();
        $crawler = $this->client->click($link);
        $this->assertTrue($this->client->getResponse()->headers->contains('Content-Disposition', 'filename=moveTest.txt'));
        //delete file number 2    
        $crawler = $this->client->request('GET', '/document/list');
        $link = $crawler->filter('#link_delete')->eq(1)->link();
        $crawler = $this->client->click($link);
        //count files
        $crawler = $this->client->request('GET', '/document/list');
        //test
        $this->assertEquals(1, $crawler->filter('#document_item')->count());
        $this->assertEquals(2, count($this->parseDirectory($this->dir)));
        $lastFiles = $this->parseDirectory($this->dir);
        $this->assertEquals("moveTest.txt", $lastFiles[0]);
    }

    public function parseDirectory($dir)
    {
        $iterator = new \DirectoryIterator($dir);
        $dirArray = array();

        foreach ($iterator as $file)
        {
            if ($file->isFile())
            {
                $dirArray[] = $file->getFilename();
            }
        }

        return $dirArray;
    }

    public function cleanDirectory($dirArray, $dir)
    {
        $ds = DIRECTORY_SEPARATOR;
        $indexCount = count($dirArray);

        for ($index = 0; $index < $indexCount; $index++)
        {
            if ($dirArray[$index] != 'placeholder')
            {
                $pathName = $dir . $ds . $dirArray[$index];
                chmod($pathName, 0777);
                unlink($pathName);
            }
        }
    }

}
