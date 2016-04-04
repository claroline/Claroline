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

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class DatabaseWriterTest extends MockeryTestCase
{
    private $om;
    private $im;
    private $mm;
    private $fileSystem;
    private $kernelRootDir;
    private $templateDir;
    private $kernel;
    private $dbWriter;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->im = $this->mock('Claroline\CoreBundle\Manager\IconManager');
        $this->mm = $this->mock('Claroline\CoreBundle\Manager\MaskManager');
        $this->fileSystem = $this->mock('Symfony\Component\Filesystem\Filesystem');
        $this->kernel = $this->mock('Symfony\Component\HttpKernel\KernelInterface');
        $this->templateDir = 'path/to/templateDir';
        $this->kernel->shouldReceive('getRootDir')->andReturn('kernelRootDir');
        $this->kernel->shouldReceive('getEnvironment')->andReturn('test');
        $this->kernelRootDir = 'kernelRootDir';
        $this->dbWriter = new DatabaseWriter(
            $this->om,
            $this->im,
            $this->fileSystem,
            $this->kernel,
            $this->mm,
            $this->templateDir
        );
    }

    public function testPersistCustomActionIfDecodersAreFound()
    {
        $this->markTestSkipped('Database writer should be refactored and properly tested');
        $resourceType = new \Claroline\CoreBundle\Entity\Resource\ResourceType();
        $actions = array(array('name' => 'open', 'menu_name' => 'open'));
        $decoder = new \Claroline\CoreBundle\Entity\Resource\MaskDecoder();
        $decoderRepo = $this->mock('Doctrine\ORM\EntityRepository');
        $decoderRepo->shouldReceive('findBy')->with(array('resourceType' => $resourceType))
            ->andReturn(array($decoder));
        $decoderRepo->shouldReceive('findOneBy')
            ->with(array('name' => 'open', 'resourceType' => $resourceType))
            ->andReturn($decoder);
        $this->em->shouldReceive('persist')->once();
    }

    public function testPersistCustomActionIfDecodersAreUnknown()
    {
        $this->markTestSkipped('Database writer should be refactored and properly tested');
    }
}
