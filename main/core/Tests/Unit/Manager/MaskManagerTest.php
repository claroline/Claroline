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

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\MenuAction;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class MaskManagerTest extends MockeryTestCase
{
    private $om;
    private $maskRepo;
    private $menuRepo;
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->maskRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->menuRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\MaskDecoder')
            ->andReturn($this->maskRepo);
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\MenuAction')
            ->andReturn($this->menuRepo);
        $this->manager = new MaskManager($this->om);
    }

    public function testEncodeMask()
    {
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');

        $openDecoder = new MaskDecoder();
        $openDecoder->setValue(1);
        $openDecoder->setName('open');

        $editDecoder = new MaskDecoder();
        $editDecoder->setValue(2);
        $editDecoder->setName('edit');

        $deleteDecoder = new MaskDecoder();
        $deleteDecoder->setValue(4);
        $deleteDecoder->setName('delete');

        $copyDecoder = new MaskDecoder();
        $copyDecoder->setValue(8);
        $copyDecoder->setName('copy');

        $exportDecoder = new MaskDecoder();
        $exportDecoder->setValue(16);
        $exportDecoder->setName('export');

        $decoders = [$openDecoder, $editDecoder, $deleteDecoder, $copyDecoder, $exportDecoder];

        foreach ($decoders as $decoder) {
            $decoder->setResourceType($resourceType);
        }

        $this->maskRepo->shouldReceive('findBy')->once()
            ->with(['resourceType' => $resourceType])->andReturn($decoders);

        $perms = [
            'open' => true,
            'edit' => false,
            'delete' => true,
            'copy' => true,
            'export' => true,
        ];

        $expectedMask = 1 + 8 + 4 + 16;

        $this->assertEquals($expectedMask, $this->manager->encodeMask($perms, $resourceType));
    }

    public function testDecodeMask()
    {
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');

        $openDecoder = new MaskDecoder();
        $openDecoder->setValue(1);
        $openDecoder->setName('open');

        $editDecoder = new MaskDecoder();
        $editDecoder->setValue(2);
        $editDecoder->setName('edit');

        $deleteDecoder = new MaskDecoder();
        $deleteDecoder->setValue(4);
        $deleteDecoder->setName('delete');

        $copyDecoder = new MaskDecoder();
        $copyDecoder->setValue(8);
        $copyDecoder->setName('copy');

        $exportDecoder = new MaskDecoder();
        $exportDecoder->setValue(16);
        $exportDecoder->setName('export');

        $decoders = [$openDecoder, $editDecoder, $deleteDecoder, $copyDecoder, $exportDecoder];

        foreach ($decoders as $decoder) {
            $decoder->setResourceType($resourceType);
        }

        $this->maskRepo->shouldReceive('findBy')->once()
            ->with(['resourceType' => $resourceType])->andReturn($decoders);

        $perms = [
            'open' => true,
            'edit' => false,
            'delete' => true,
            'copy' => true,
            'export' => true,
        ];

        $mask = $this->manager->encodeMask($perms, $resourceType);
        $this->assertEquals($perms, $this->manager->decodeMask($mask, $resourceType));
    }

    public function testPermissionMap()
    {
        $openDecoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $editDecoder = $this->mock('Claroline\CoreBundle\Entity\Resource\MaskDecoder');
        $decoders = [$openDecoder, $editDecoder];
        $openDecoder->shouldReceive('getName')->andReturn('open');
        $editDecoder->shouldReceive('getName')->andReturn('edit');
        $type = new ResourceType();
        $this->maskRepo->shouldReceive('findBy')->once()->with(['resourceType' => $type])->andReturn($decoders);

        $this->assertEquals(['open', 'edit'], $this->manager->getPermissionMap($type));
    }

    public function testGetDecoder()
    {
        $type = new ResourceType();
        $action = 'action';
        $decoder = new MaskDecoder();
        $this->maskRepo->shouldReceive('findOneBy')->with(['resourceType' => $type, 'name' => $action])
            ->andReturn($decoder);
        $this->assertEquals($decoder, $this->manager->getDecoder($type, $action));
    }

    public function testGetByValue()
    {
        $type = new ResourceType();
        $value = 42;
        $decoder = new MaskDecoder();
        $this->maskRepo->shouldReceive('findOneBy')->with(['resourceType' => $type, 'value' => $value])
            ->andReturn($decoder);
        $this->assertEquals($decoder, $this->manager->getByValue($type, $value));
    }

    public function testGetMenuFromNameAndResourceType()
    {
        $type = new ResourceType();
        $name = 'menu';
        $menu = new MenuAction();
        $this->menuRepo->shouldReceive('findOneBy')->with(['resourceType' => $type, 'name' => $name])
            ->andReturn($menu);
        $this->assertEquals($menu, $this->manager->getMenuFromNameAndResourceType($name, $type));
    }

    public function testAddDefaultPerms()
    {
        $type = new ResourceType();
        $this->om->shouldReceive('persist')
            ->times(5)
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Resource\MaskDecoder'));
        $this->om->shouldReceive('persist')
            ->times(5)
            ->with(anInstanceOf('Claroline\CoreBundle\Entity\Resource\MenuAction'));
        $this->om->shouldReceive('flush')->once();
        $this->manager->addDefaultPerms($type);
    }
}
