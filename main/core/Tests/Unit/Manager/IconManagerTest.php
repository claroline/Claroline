<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\ResourceIcon;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Mockery as m;

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
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
    }

    /**
     * @group resource
     */
    public function testGetIconForDirectory()
    {
        $manager = $this->getManager(['searchIcon']);
        $dir = $this->mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $dir->shouldReceive('getResourceNode')->once()->andReturn($node);
        $node->shouldReceive('getMimeType')->andReturn('custom/directory');
        $icon = new ResourceIcon();
        $manager->shouldReceive('searchIcon')->once()->andReturn($icon);
        $this->assertEquals($icon, $manager->getIcon($dir));
    }

    /**
     * @group resource
     */
    public function testGetIconForFile()
    {
        $manager = $this->getManager(['createFromFile', 'getEntity']);
        $file = $this->mock('Claroline\CoreBundle\Entity\Resource\File');
        $node = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $file->shouldReceive('getResourceNode')->once()->andReturn($node);
        $node->shouldReceive('getMimeType')->once()->andReturn('video/mp4');
        $file->shouldReceive('getHashName')->once()->andReturn('ABCDEFG.mp4');
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $icon->shouldReceive('setMimeType')->once()->with('custom');
        $icon->shouldReceive('setRelativeUrl')->once()->with('thumbnails/thumbnail');
        $icon->shouldReceive('setShortcut')->once()->with(false);
        $manager->shouldReceive('createFromFile')->once()
            ->with($this->fileDir.DIRECTORY_SEPARATOR.'ABCDEFG.mp4', 'video')
            ->andReturn('path/to/thumbnail');
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
        $icon = new ResourceIcon();
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
    public function testCreateFromVideoFile()
    {
        $filePath = 'path/to/file';
        $baseMime = 'video';
        $this->ut->shouldReceive('generateGuid')->once()->andReturn('GUID');
        $this->thumbnailCreator->shouldReceive('fromVideo')->once()
            ->with($filePath, $this->thumbDir.DIRECTORY_SEPARATOR.'GUID.png', 100, 100)
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
            ->with($filePath, $this->thumbDir.DIRECTORY_SEPARATOR.'GUID.png', 100, 100)
            ->andReturn('path/to/thumbnail');

        $this->assertEquals('path/to/thumbnail', $this->getManager()->createFromFile($filePath, $baseMime));
    }

    public function testDeleteCustom()
    {
        $manager = $this->getManager(['removeImageFromThumbDir']);
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
        $manager = $this->getManager(['delete']);
        $this->om->shouldReceive('startFlushSuite')->once();
        $icon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $oldIcon = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceIcon');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $resource->shouldReceive('getIcon')->once()->andReturn($oldIcon);
        $resource->shouldReceive('setIcon')->once()->with($icon);
        $manager->shouldReceive('delete')->once()->with($oldIcon);
        $this->om->shouldReceive('persist')->once()->with($resource);
        $this->om->shouldReceive('endFlushSuite')->once();

        $this->assertEquals($resource, $manager->replace($resource, $icon));
    }

    public function testRemoveImageFromThumbDir()
    {
        $this->markTestSkipped('Requires stubs');
    }

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\ResourceIcon')
            ->andReturn($this->repo);

        if (0 === count($mockedMethods)) {
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
                'Claroline\CoreBundle\Manager\IconManager'.$stringMocked,
                [
                    $this->thumbnailCreator,
                    $this->fileDir,
                    $this->thumbDir,
                    $this->rootDir,
                    $this->ut,
                    $this->om,
                ]
            );
        }
    }
}
