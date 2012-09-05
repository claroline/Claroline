<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Tests\DataFixtures\LoadResourceTypeData;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class IconCreatorTest extends FixtureTestCase
{
    private $videoPath;
    private $imagePath;
    private $thumbDir;
    private $iconCreator;
    private $fileType;
    private $areLoaded;

    protected function setUp()
    {
        parent::setUp();
        $this->loadFixture(new LoadResourceTypeData());
        $ds = DIRECTORY_SEPARATOR;
        if( extension_loaded('gd') && extension_loaded('ffmpeg')){
            $this->areLoaded = true;
        }

        $this->videoPath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}video.mp4";
        $copyVideoPath = "{$this->client->getContainer()->getParameter('claroline.files.directory')}{$ds}video.mp4";
        copy($this->videoPath, $copyVideoPath);

        $this->imagePath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}image.jpg";
        $copyImagePath = "{$this->client->getContainer()->getParameter('claroline.files.directory')}{$ds}image.jpg";
        copy($this->imagePath, $copyImagePath);

        $this->textPath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}text.txt";
        $copyTestPath = "{$this->client->getContainer()->getParameter('claroline.files.directory')}{$ds}text.txt";
        copy($this->textPath, $copyTestPath);

        $this->thumbDir = $this->client->getContainer()->getParameter('claroline.thumbnails.directory');
        $this->iconCreator = $this->client->getContainer()->get('claroline.resource.icon_creator');

        $this->fileType = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'file'));
    }

    protected function tearDown()
    {
        $this->cleanDirectory($this->client->getContainer()->getParameter('claroline.files.directory'));
        $this->cleanDirectory($this->thumbDir);
        parent::tearDown();
    }

    public function testFileWithoutMimeThrowsAnException()
    {
        $this->setExpectedException('InvalidArgumentException');
        $file = new File();
        $file->setResourceType($this->fileType);
        $this->iconCreator->setResourceIcon($file);
    }

    public function testCreateVideoThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('video.mp4');
        $file = $this->iconCreator->setResourceIcon($file, 'video/mp4');
        if ($this->areLoaded) {
            $thumbs = $this->getUploadedFiles($this->thumbDir);
            $this->assertEquals(1, count($thumbs));
        } else {
            $name = $file->getIcon()->getLargeIcon();
            $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_video.png', $name);
        }
    }

    public function testCreateImageThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('image.jpg');
        $file = $this->iconCreator->setResourceIcon($file, 'image/jpg');
        if (extension_loaded('gd')) {
            $thumbs = $this->getUploadedFiles($this->thumbDir);
            $this->assertEquals(1, count($thumbs));
        } else {
            $name = $file->getIcon()->getLargeIcon();
            $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_image.png', $name);
        }
    }

    public function testUnknownVideoMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('video.mp4');
        $file = $this->iconCreator->setResourceIcon($file, 'video/ThatOneDoesntExists');
        $name = $file->getIcon()->getLargeIcon();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_video.png', $name);
    }

    public function testUnknownImageMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('image.jpg');
        $file = $this->iconCreator->setResourceIcon($file, 'image/ThatOneDoesntExists');
        $name = $file->getIcon()->getLargeIcon();
        //no res_image yet
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_file.png', $name);
    }

    public function testFileGetCompleteMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('text.txt');
        $file = $this->iconCreator->setResourceIcon($file, 'text/plain');
        $name = $file->getIcon()->getLargeIcon();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_text.png', $name);
    }

    public function testFileGetBasicMimeThumbnail()
    {
        $this->markTestSkipped('tested with the unknown video mime');
    }

    public function testFileGetUnknownMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('text.txt');
        $file = $this->iconCreator->setResourceIcon($file, 'INeverSaw/ThatMimeType');
        $name = $file->getIcon()->getLargeIcon();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_file.png', $name);
    }

    public function testGetTypeDefinedThumbnail()
    {
        $dir = new Directory();
        $dirType = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceType')
            ->findOneBy(array('type' => 'directory'));
        $dir->setResourceType($dirType);
        $dir = $this->iconCreator->setResourceIcon($dir);
        $name = $dir->getIcon()->getLargeIcon();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_folder.png', $name);
    }

    public function testGetTypeUndefinedThumbnail()
    {
        $dir = new Directory();
        $undefinedType = new ResourceType();
        $undefinedType->setType('undefined');
        $dir->setResourceType($undefinedType);
        $dir = $this->iconCreator->setResourceIcon($dir);
        $name = $dir->getIcon()->getLargeIcon();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/large/res_default.png', $name);

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
