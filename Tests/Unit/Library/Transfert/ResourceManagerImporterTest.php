<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\ResourceManagerImporter;
use Symfony\Component\Yaml\Yaml;

class ResourceManagerImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;
    private $importers;
    private $fileImporter;
    private $textImporter;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->importer = new ResourceManagerImporter($this->om);
        $confFile = __DIR__.'/../../../Stub/transfert/valid/full/manifest.yml';
        $this->importer->setConfiguration(Yaml::parse(file_get_contents($confFile)));

        $this->fileImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Resources\FileImporter');
        $this->fileImporter->shouldReceive('getName')->andReturn('file');
        $this->textImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Resources\TextImporter');
        $this->textImporter->shouldReceive('getName')->andReturn('text');

        $this->importers = new ArrayCollection();
        $this->importers->add($this->fileImporter);
        $this->importers->add($this->textImporter);
        $this->importer->setListImporters($this->importers);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $resolver = new Resolver($path, 'tools/resource_manager.yml');
        $data = $resolver->resolve();
        $resources['data'] = $data['data'];

        $this->fileImporter->shouldReceive('validate')->andReturn(true);
        $this->textImporter->shouldReceive('validate')->andReturn(true);

        $this->importer->validate($resources);
    }

    /* @todo add validations */
    public function validateProvider()
    {
        return array(
            //correct
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => false
            )
            //roles don't exist

            //unknown resource not found

            //parent don't exist
        );
    }
}