<?php

namespace Claroline\CoreBundle\Library\Resource;

use Claroline\CoreBundle\Library\Testing\FixtureTestCase;
use Claroline\CoreBundle\Entity\Resource\File;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceType;

class IconCreatorTest extends FixtureTestCase
{
    private $thumbDir;
    private $iconCreator;
    private $fileType;
    private $areLoaded;

    protected function setUp()
    {
        parent::setUp();
        $ds = DIRECTORY_SEPARATOR;

        if (extension_loaded('gd') && extension_loaded('ffmpeg')) {
            $this->areLoaded = true;
        }

        $videoPath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}video.mp4";
        $copyVideoPath = $this->client->getContainer()->getParameter('claroline.param.files_directory').$ds."video.mp4";
        copy($videoPath, $copyVideoPath);

        $videoPath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}video.unknownExtension";
        $copyVideoPath = $this->client->getContainer()->getParameter('claroline.param.files_directory').$ds
            . "video.unknownExtension";
        copy($videoPath, $copyVideoPath);

        $imagePath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}image.jpg";
        $copyImagePath = $this->client->getContainer()->getParameter('claroline.param.files_directory').$ds."image.jpg";
        copy($imagePath, $copyImagePath);

        $imagePath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}image.unknownExtension";
        $copyImagePath = $this->client->getContainer()->getParameter('claroline.param.files_directory').$ds
            . "image.unknownExtension";
        copy($imagePath, $copyImagePath);

        $textPath = __DIR__ . "..{$ds}..{$ds}..{$ds}Stub{$ds}files{$ds}text.txt";
        $copyTestPath = "{$this->client->getContainer()->getParameter('claroline.param.files_directory')}{$ds}text.txt";
        copy($textPath, $copyTestPath);

        $this->thumbDir = $this->client->getContainer()->getParameter('claroline.param.thumbnails_directory');
        $this->iconCreator = $this->client->getContainer()->get('claroline.resource.icon_creator');

        $this->fileType = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => 'file'));
    }

    protected function tearDown()
    {
        $this->cleanDirectory($this->client->getContainer()->getParameter('claroline.param.files_directory'));
        $this->cleanDirectory($this->thumbDir);
        parent::tearDown();
    }

    public function testFileWithoutMimeThrowsAnException()
    {
        $this->setExpectedException('RuntimeException');
        $file = new File();
        $file->setResourceType($this->fileType);
        $this->iconCreator->setResourceIcon($file);
    }

    public function testCreateVideoThumbnail()
    {
        ob_start();
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('video.mp4');
        $file->setMimeType('video/mp4');
        $file = $this->iconCreator->setResourceIcon($file);

        if ($this->areLoaded) {
            $thumbs = $this->getUploadedFiles($this->thumbDir);
            $this->assertEquals(2, count($thumbs));
        } else {
            $name = $file->getIcon()->getRelativeUrl();
            $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_video.png', $name);
        }

        ob_end_clean();
    }

    public function testCreateImageThumbnail()
    {
        ob_start();
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('image.jpg');
        $file->setMimeType('image/jpg');
        $this->iconCreator->setResourceIcon($file);

        if (extension_loaded('gd')) {
            $thumbs = $this->getUploadedFiles($this->thumbDir);
            $this->assertEquals(2, count($thumbs));
        } else {
            $name = $file->getIcon()->getIconLocation();
            $this->assertEquals('bundles/clarolinecore/images/resources/large/res_image.jpg', $name);
        }

        ob_end_clean();
    }

    public function testUnknownVideoMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('video.unknownExtension');
        $file->setMimeType('video/ThatOneDoesntExists');
        $file = $this->iconCreator->setResourceIcon($file);
        $name = $file->getIcon()->getRelativeUrl();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_video.png', $name);
    }

    public function testUnknownImageMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('image.unknownExtension');
        $file->setMimeType('image/ThatOneDoesntExists');
        $file = $this->iconCreator->setResourceIcon($file);
        $name = $file->getIcon()->getRelativeUrl();
        //no res_image yet
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_image.png', $name);
    }

    public function testFileGetCompleteMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('text.txt');
        $file->setMimeType('text/plain');
        $file = $this->iconCreator->setResourceIcon($file);
        $name = $file->getIcon()->getRelativeUrl();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_text.png', $name);
    }

    public function testFileGetUnknownMimeThumbnail()
    {
        $file = new File();
        $file->setResourceType($this->fileType);
        $file->setHashName('text.unknownMimeTypeIsBeautifull');
        $file->setMimeType('INeverSaw/ThatMimeType');
        $file = $this->iconCreator->setResourceIcon($file);
        $name = $file->getIcon()->getRelativeUrl();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_file.png', $name);
    }

    public function testGetTypeDefinedThumbnail()
    {
        $dir = new Directory();
        $dirType = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->findOneBy(array('name' => 'directory'));
        $dir->setResourceType($dirType);
        $dir = $this->iconCreator->setResourceIcon($dir);
        $name = $dir->getIcon()->getRelativeUrl();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_folder.png', $name);
    }

    public function testGetTypeUndefinedThumbnail()
    {
        $dir = new Directory();
        $undefinedType = new ResourceType();
        $undefinedType->setName('undefined');
        $dir->setResourceType($undefinedType);
        $dir = $this->iconCreator->setResourceIcon($dir);
        $name = $dir->getIcon()->getRelativeUrl();
        $this->assertEquals('bundles/clarolinecore/images/resources/icons/res_default.png', $name);

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
