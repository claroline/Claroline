<?php
namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile as SfFile;
use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\DataFixtures\LoadMimeTypeData;
use Claroline\CoreBundle\Entity\Resource\File;

class FileControllerTest extends FunctionalTestCase
{
    /** @var string */
    private $upDir;

    /** @var string */
    private $stubDir;

    public function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadResourceTypeData());
        $this->loadUserFixture();
        $this->client->followRedirects();
        $ds = DIRECTORY_SEPARATOR;
        $this->stubDir = __DIR__ . "{$ds}..{$ds}Stub{$ds}files{$ds}";
        $this->upDir  = $this->client->getContainer()->getParameter('claroline.files.directory');
        $this->cleanDirectory($this->upDir);
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->cleanDirectory($this->upDir);
    }

    public function testUpload()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $originalPath = $this->stubDir.'originalFile.txt';
         $ri = $this->uploadFile($originalPath, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
         $this->client->request(
            'POST',
            "/resource/node/0/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}/node.json"
        );
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(1, count($file));
        $this->assertEquals(1, count($this->getUploadedFiles()));
    }

    public function testDownload()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $originalPath = $this->stubDir.'originalFile.txt';
         $ri = $this->uploadFile($originalPath, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
         $this->client->request(
            'GET',
            "/resource/click/{$ri->getId()}"
        );
         $headers = $this->client->getResponse()->headers;
         $this->assertTrue($headers->contains('Content-Disposition', 'attachment; filename=copy.txt'));
    }

    public function testDelete()
    {
         $this->logUser($this->getFixtureReference('user/user'));
         $originalPath = $this->stubDir.'originalFile.txt';
         $ri = $this->uploadFile($originalPath, $this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId());
         $this->client->request(
            'GET',
            "/resource/workspace/remove/{$ri->getId()}/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}"
         );
        $this->client->request(
            'POST',
            "/resource/node/0/{$this->getFixtureReference('user/user')->getPersonnalWorkspace()->getId()}/node.json"
        );
        $file = json_decode($this->client->getResponse()->getContent());
        $this->assertEquals(0, count($file));
        $this->assertEquals(0, count($this->getUploadedFiles()));
    }

    private function uploadFile($filePath, $workspaceId, $parentId = null)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $copyPath = $this->stubDir."copy".$extension;
        copy($filePath, $copyPath);
        $file = new SfFile($copyPath, "copy.$extension", null, null, null, true);
        $object = new File();
        $object->setName($file);
        $object->setShareType(1);

        return $this->addResource($object, $workspaceId);
    }

    private function addResource($object, $workspaceId, $parentId = null)
    {
        return $ri = $this
            ->client
            ->getContainer()
            ->get('claroline.resource.creator')
            ->createResource(
                $object,
                $workspaceId,
                $parentId,
                true
                );
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
}
