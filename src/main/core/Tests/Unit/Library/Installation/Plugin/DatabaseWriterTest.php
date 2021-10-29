<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Plugin;

use Claroline\CoreBundle\Entity\Resource\MaskDecoder;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class DatabaseWriterTest extends MockeryTestCase
{
    private $om;
    private $mm;
    private $fileSystem;
    private $dbWriter;

    public function setUp(): void
    {
        parent::setUp();

        \Mockery::getConfiguration()->allowMockingNonExistentMethods(true); // needed for mocking the `Kernel::getProjectDir()` method (virtual until symfony 5)
        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->om->shouldReceive('getRepository')->andReturn($this->mock('Doctrine\ORM\EntityRepository'));
        $this->mm = $this->mock('Claroline\CoreBundle\Manager\Resource\MaskManager');
        $this->tm = $this->mock('Claroline\CoreBundle\Manager\Tool\ToolManager');
        $this->tmd = $this->mock('Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager');
        $this->ism = $this->mock('Claroline\ThemeBundle\Manager\IconSetManager');
        $this->fileSystem = $this->mock('Symfony\Component\Filesystem\Filesystem');
        $this->dbWriter = new DatabaseWriter(
            $this->om,
            $this->mm,
            $this->fileSystem,
            $this->tm,
            $this->tmd,
            $this->ism
        );
    }

    public function tearDown(): void
    {
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
    }

    public function testPersistCustomActionIfDecodersAreFound()
    {
        $this->markTestSkipped('Database writer should be refactored and properly tested');
        $resourceType = new ResourceType();
        $decoder = new MaskDecoder();
        $decoderRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $decoderRepo->shouldReceive('findBy')->with(['resourceType' => $resourceType])
            ->andReturn([$decoder]);
        $decoderRepo->shouldReceive('findOneBy')
            ->with(['name' => 'open', 'resourceType' => $resourceType])
            ->andReturn($decoder);
        $this->om->shouldReceive('persist')->once();
    }

    public function testPersistCustomActionIfDecodersAreUnknown()
    {
        $this->markTestSkipped('Database writer should be refactored and properly tested');
    }
}
