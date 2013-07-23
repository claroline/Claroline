<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Manager\IconManager;

class IconManagerTest extends MockeryTestCase
{
    private $thumbnailCreator;
    private $repo;
    private $fileDir;
    private $thumbDir;
    private $rootDir;
    private $ut;
    private $om;

    public function setUp()
    {
        parent::setUp();
        $this->thumbnailCreator = $this->mock('Claroline\CoreBundle\Library\Utilities\ThumbnailCreator');
        $this->repo = $this->mock('Claroline\CoreBundle\Repository\ResourceIconRepository');
        $this->fileDir = 'path/to/filedir';
        $this->thumbDir = 'path/to/thumbdir';
        $this->rootDor = 'path/to/rootDir';
        $this->ut = $this->mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    /**
     * @group resource
     */
    public function testGetIconForDirectory()
    {
        $manager = $this->getManager(array('searchIcon'));
        $dir = $this->mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $dir->shouldReceive('getMimeType')->andReturn('custom/directory');
        $icon = new ResourceIcon();
        $manager->shouldReceive('searchIcon')->once()->andReturn($icon);
        $this->assertEquals($icon, $manager->getIcon($dir));
    }

    /**
     * @group resource
     */
    public function testGetIconForFile()
    {
        $manager = $this->getManager(array('createFromFile', 'createShortcutIcon', 'getEntity'));
        $file = $this->mock('Claroline\CoreBundle\Entity\Resource\File');
        $file->shouldReceive('getMimeType')->once()->andReturn('video/mp4');
        $file->shouldReceive('getHashName')->once()->andReturn('ABCDEFG.mp4');
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->shouldReceive('setMimeType')->once()->with('custom');
        $icon->shouldReceive('setIconLocation')->once()->with('path/to/thumbnail');
        $icon->shouldReceive('setRelativeUrl')->once()->with('thumbnails/thumbnail');
        $icon->shouldReceive('setShortcut')->once()->with(false);
        $manager->shouldReceive('createFromFile')->once()
            ->with($this->fileDir . DIRECTORY_SEPARATOR . 'ABCDEFG.mp4', 'video')
            ->andReturn('path/to/thumbnail');
        $manager->shouldReceive('createShortcutIcon')->once()->with($icon);
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('endFlushSuite')->once();
        $this->om->shouldReceive('persist')->with($icon)->once();
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->andReturn($icon);

        $this->assertEquals($icon, $manager->getIcon($file));
    }

    /**
     * @group resource
     */
    public function testSearchIcon()
    {
        $icon = new \Claroline\CoreBundle\Entity\Resource\ResourceIcon();
        $mimeType = 'video/mp4';
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->repo->shouldReceive('findOneByMimeType')->with($mimeType)->once()->andReturn(null);
        $this->repo->shouldReceive('findOneByMimeType')->with('video')->once()->andReturn(null);
        $this->repo->shouldReceive('findOneByMimeType')->with('custom/default')->once()->andReturn($icon);

        $this->assertEquals($icon, $this->getManager()->searchIcon($mimeType));
    }

    /**
     * @group resource
     */
    public function testCreateShortcutIcon()
    {
        $manager = $this->getManager(array('getEntity'));
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $shortcutIcon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->andReturn($shortcutIcon);
        $icon->shouldReceive('getIconLocation')->once()->andReturn('/path/to/icon/location');
        $icon->shouldReceive('getMimeType')->once()->andReturn('video/mp4');
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->thumbnailCreator->shouldReceive('shortcutThumbnail')->once()->with('/path/to/icon/location')
            ->andReturn('/path/to/bundles/shortcut/location');
        $shortcutIcon->shouldReceive('setIconLocation')->with('/path/to/bundles/shortcut/location')->once();
        $shortcutIcon->shouldReceive('setRelativeUrl')->with('bundles/shortcut/location');
        $shortcutIcon->shouldReceive('setMimeType')->once()->with('video/mp4');
        $shortcutIcon->shouldReceive('setShortcut')->once()->with(true);
        $icon->shouldReceive('setShortcutIcon')->once()->with($shortcutIcon);
        $shortcutIcon->shouldReceive('setShortcutIcon')->once()->with($shortcutIcon);
        $this->om->shouldReceive('persist')->once()->with($icon);
        $this->om->shouldReceive('persist')->once()->with($shortcutIcon);
        $this->om->shouldReceive('endFlushSuite');

        $this->assertEquals($shortcutIcon, $manager->createShortcutIcon($icon));
    }

    /**
     * @group resource
     */
    public function testCreateCustomnIcon()
    {
        $manager = $this->getManager(array('getEntity', 'createShortcutIcon'));
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $file = $this->mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        $file->shouldReceive('getClientOriginalName')->once()->andReturn('original/name.ext');
        $this->ut->shouldReceive('generateGuid')->andReturn('ABCDEF')->once();
        $file->shouldReceive('move')->once()->with($this->thumbDir, 'ABCDEF.ext');
        $icon->shouldReceive('setIconLocation')->once()
            ->with($this->thumbDir . DIRECTORY_SEPARATOR . 'ABCDEF.ext');
        $icon->shouldReceive('setRelativeUrl')->once()
            ->with('thumbnails' . DIRECTORY_SEPARATOR . 'ABCDEF.ext');
        $icon->shouldReceive('setMimeType')->once()->with('custom');
        $icon->shouldReceive('setShortcut')->once()->with(false);
        $manager->shouldReceive('createShortcutIcon')->once()->with($icon);
        $this->om->shouldReceive('startFlushSuite');
        $this->om->shouldReceive('endFlushSuite');
        $this->om->shouldReceive('persist')->once()->with($icon);
        $this->om->shouldReceive('factory')->once()
            ->with('Claroline\CoreBundle\Entity\Resource\ResourceIcon')
            ->andReturn($icon);
        $this->assertEquals($icon, $manager->createCustomIcon($file));
    }

    /**
     * @group resource
     */
    public function testCreateFromVideoFile()
    {
        $filePath = 'path/to/file';
        $baseMime = 'video';
        $this->ut->shouldReceive('generateGuid')->once()->andReturn('GUID');
        $this->thumbnailCreator->shouldReceive('fromVideo')->once()
            ->with($filePath, $this->thumbDir . DIRECTORY_SEPARATOR . 'GUID.png', 100, 100)
            ->andReturn('path/to/thumbnail');

        $this->assertEquals('path/to/thumbnail', $this->getManager()->createFromFile($filePath, $baseMime));
    }

    /**
     * @group resource
     */
    public function testCreateFromImageFile()
    {
        $filePath = 'path/to/file';
        $baseMime = 'image';
        $this->ut->shouldReceive('generateGuid')->once()->andReturn('GUID');
        $this->thumbnailCreator->shouldReceive('fromImage')->once()
            ->with($filePath, $this->thumbDir . DIRECTORY_SEPARATOR . 'GUID.png', 100, 100)
            ->andReturn('path/to/thumbnail');

        $this->assertEquals('path/to/thumbnail', $this->getManager()->createFromFile($filePath, $baseMime));
    }

    public function testDeleteCustom()
    {
        $manager = $this->getManager(array('removeImageFromThumbDir'));
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $shortcut = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->shouldReceive('getShortcutIcon')->once()->andReturn($shortcut);
        $icon->shouldReceive('getMimeType')->once()->andReturn('custom');
        $manager->shouldReceive('removeImageFromThumbDir')->once()->with($icon);
        $manager->shouldReceive('removeImageFromThumbDir')->once()->with($shortcut);
        $this->om->shouldReceive('remove')->once()->with($shortcut);
        $this->om->shouldReceive('remove')->once()->with($icon);
        $this->om->shouldReceive('flush')->once();

        $manager->delete($icon);
    }

    public function testReplace()
    {
        $manager = $this->getManager(array('delete'));
        $this->om->shouldReceive('startFlushSuite')->once();
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $oldIcon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\AbstractResource');
        $resource->shouldReceive('getIcon')->once()->andReturn($oldIcon);
        $resource->shouldReceive('setIcon')->once()->with($icon);
        $manager->shouldReceive('delete')->once()->with($oldIcon);
        $this->om->shouldReceive('endFlushSuite')->once();

        $this->assertEquals($resource, $manager->replace($resource, $icon));
    }

    public function testRemoveImageFromThumbDir()
    {
        $this->markTestSkipped('Requires stubs');
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceIcon')
            ->andReturn($this->repo);

        if (count($mockedMethods) === 0) {
            return new IconManager(
                $this->thumbnailCreator,
                $this->fileDir,
                $this->thumbDir,
                $this->rootDir,
                $this->ut,
                $this->om
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Manager\IconManager' . $stringMocked,
                array(
                    $this->thumbnailCreator,
                    $this->fileDir,
                    $this->thumbDir,
                    $this->rootDir,
                    $this->ut,
                    $this->om
                )
            );
        }
    }
}
