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
    private $writer;
    private $rootDir;
    private $ut;
    
    public function setUp()
    {
        parent::setUp();
        $this->thumbnailCreator = m::mock('Claroline\CoreBundle\Library\Utilities\ThumbnailCreator');
        $this->repo = m::mock('Claroline\CoreBundle\Repository\ResourceIconRepository');
        $this->fileDir = 'path/to/filedir';
        $this->thumbDir = 'path/to/thumbdir';
        $this->writer = m::mock('Claroline\CoreBundle\Database\Writer');
        $this->rootDor = 'path/to/rootDir';
        $this->ut = m::mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
    }
    
    /**
     * @group resource
     */
    public function testGetIconForDirectory()
    {
        $manager = $this->getManager(array('searchIcon'));
        $dir = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
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
        $file = m::mock('Claroline\CoreBundle\Entity\Resource\File');
        $file->shouldReceive('getMimeType')->once()->andReturn('video/mp4');
        $file->shouldReceive('getHashName')->once()->andReturn('ABCDEFG.mp4');
        $icon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->shouldReceive('setMimeType')->once()->with('custom');
        $icon->shouldReceive('setIconLocation')->once()->with('path/to/thumbnail');
        $icon->shouldReceive('setRelativeUrl')->once()->with('thumbnails/thumbnail');
        $icon->shouldReceive('setShortcut')->once()->with(false);
        $manager->shouldReceive('createFromFile')->once()
            ->with($this->fileDir . DIRECTORY_SEPARATOR . 'ABCDEFG.mp4', 'video')
            ->andReturn('path/to/thumbnail');
        $manager->shouldReceive('getEntity')->once()->andReturn($icon);
        $this->writer->shouldReceive('create')->once()->with($icon);
        $manager->shouldReceive('createShortcutIcon')->once()->with($icon);
        
        $manager->getIcon($file);
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
        $icon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $shortcutIcon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->shouldReceive('getIconLocation')->once()->andReturn('/path/to/icon/location');
        $icon->shouldReceive('getMimeType')->once()->andReturn('video/mp4');
        $this->writer->shouldReceive('suspendFlush')->once();
        $this->thumbnailCreator->shouldReceive('shortcutThumbnail')->once()->with('/path/to/icon/location')
            ->andReturn('/path/to/bundles/shortcut/location');
        $manager->shouldReceive('getEntity')->once()->with('Resource\ResourceIcon')->andReturn($shortcutIcon);
        $shortcutIcon->shouldReceive('setIconLocation')->with('/path/to/bundles/shortcut/location')->once();
        $shortcutIcon->shouldReceive('setRelativeUrl')->with('bundles/shortcut/location');
        $shortcutIcon->shouldReceive('setMimeType')->once()->with('video/mp4');
        $shortcutIcon->shouldReceive('setShortcut')->once()->with(true);
        $icon->shouldReceive('setShortcutIcon')->once()->with($shortcutIcon);
        $shortcutIcon->shouldReceive('setShortcutIcon')->once()->with($shortcutIcon);
        $this->writer->shouldReceive('update')->once()->with($icon);
        $this->writer->shouldReceive('create')->once()->with($shortcutIcon);
        $this->writer->shouldReceive('forceFlush');
        
        $this->assertEquals($shortcutIcon, $manager->createShortcutIcon($icon));
    }
    
    /**
     * @group resource
     */
    public function testCreateCustomnIcon()
    {
        $manager = $this->getManager(array('getEntity', 'createShortcutIcon'));
        $icon = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $file = m::mock('Symfony\Component\HttpFoundation\File\UploadedFile');
        $file->shouldReceive('getClientOriginalName')->once()->andReturn('original/name.ext');
        $this->ut->shouldReceive('generateGuid')->andReturn('ABCDEF')->once();
        $file->shouldReceive('move')->once()->with($this->thumbDir, 'ABCDEF.ext');
        $manager->shouldReceive('getEntity')->once()->andReturn($icon);
        $icon->shouldReceive('setIconLocation')->once()
            ->with($this->thumbDir . DIRECTORY_SEPARATOR . 'ABCDEF.ext');
        $icon->shouldReceive('setRelativeUrl')->once()
            ->with('thumbnails' . DIRECTORY_SEPARATOR . 'ABCDEF.ext');
        $icon->shouldReceive('setMimeType')->once()->with('custom');
        $icon->shouldReceive('setShortcut')->once()->with(false);
        $this->writer->shouldReceive('create')->once()->with($icon);
        $manager->shouldReceive('createShortcutIcon')->once()->with($icon);
        
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
    
    private function getManager(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new IconManager(
                $this->thumbnailCreator,
                $this->repo,
                $this->fileDir,
                $this->thumbDir,
                $this->writer,
                $this->rootDir,
                $this->ut
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';
            
            return m::mock(
                'Claroline\CoreBundle\Manager\IconManager' . $stringMocked,
                array(
                    $this->thumbnailCreator,
                    $this->repo,
                    $this->fileDir,
                    $this->thumbDir,
                    $this->writer,
                    $this->rootDir,
                    $this->ut
                )
            );
        }
    }
}