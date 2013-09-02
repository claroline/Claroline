<?php

namespace Claroline\CoreBundle\Manager;

use Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class MaskManagerTest extends MockeryTestCase
{
    private $om;
    private $maskRepo;
    private $manager;

    public function setUp()
    {
        parent::setUp();

        $this->maskRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->om->shouldReceive('getRepository')->with('ClarolineCoreBundle:Resource\MaskDecoder')
            ->andReturn($this->maskRepo);
        $this->manager = new MaskManager($this->om);
    }

    public function testEncodeMask()
    {
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');

        $openDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $openDecoder->setValue(1);
        $openDecoder->setName('open');

        $editDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $editDecoder->setValue(2);
        $editDecoder->setName('edit');

        $deleteDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $deleteDecoder->setValue(4);
        $deleteDecoder->setName('delete');

        $copyDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $copyDecoder->setValue(8);
        $copyDecoder->setName('copy');

        $exportDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $exportDecoder->setValue(16);
        $exportDecoder->setName('export');

        $decoders = array($openDecoder, $editDecoder, $deleteDecoder, $copyDecoder, $exportDecoder);

        foreach ($decoders as $decoder) {
            $decoder->setResourceType($resourceType);
        }

        $this->maskRepo->shouldReceive('findBy')->once()
            ->with(array('resourceType' => $resourceType))->andReturn($decoders);

        $perms = array(
            'open' => true,
            'edit' => false,
            'delete' => true,
            'copy' => true,
            'export' => true
        );

        $expectedMask = 1 + 8 + 4 + 16;

        $this->assertEquals($expectedMask, $this->manager->encodeMask($perms, $resourceType));
    }

    public function testDecodeMask()
    {
        $resourceType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');

        $openDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $openDecoder->setValue(1);
        $openDecoder->setName('open');

        $editDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $editDecoder->setValue(2);
        $editDecoder->setName('edit');

        $deleteDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $deleteDecoder->setValue(4);
        $deleteDecoder->setName('delete');

        $copyDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $copyDecoder->setValue(8);
        $copyDecoder->setName('copy');

        $exportDecoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $exportDecoder->setValue(16);
        $exportDecoder->setName('export');

        $decoders = array($openDecoder, $editDecoder, $deleteDecoder, $copyDecoder, $exportDecoder);

        foreach ($decoders as $decoder) {
            $decoder->setResourceType($resourceType);
        }

        $this->maskRepo->shouldReceive('findBy')->once()
            ->with(array('resourceType' => $resourceType))->andReturn($decoders);

        $perms = array(
            'open' => true,
            'edit' => false,
            'delete' => true,
            'copy' => true,
            'export' => true
        );

        $mask = $this->manager->encodeMask($perms, $resourceType);
        $this->assertEquals($perms, $this->manager->decodeMask($mask, $resourceType));
    }
}
