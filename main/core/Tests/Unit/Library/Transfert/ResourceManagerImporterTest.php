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
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\ResourceManagerImporter;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Doctrine\Common\Collections\ArrayCollection;
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

        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->importer = new ResourceManagerImporter($this->om);
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
    public function testValidate($basePath, $isExceptionExpected, $rolefile, $managerPath)
    {
        $this->importer->setConfiguration(Yaml::parse(file_get_contents($rolefile)));

        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $resolver = new Resolver($basePath, $managerPath);
        $data = $resolver->resolve();
        $resources['data'] = $data['data'];

        $this->fileImporter->shouldReceive('validate')->andReturn(true);
        $this->textImporter->shouldReceive('validate')->andReturn(true);

        $this->importer->validate($resources);
    }

    /* @todo add validations */
    public function validateProvider()
    {
        return [
            //correct
            [
                'basePath' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => false,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
                'managerPath' => 'tools/resource_manager.yml',
            ],
            //roles don't exist
            [
                'basePath' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => true,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles02.yml',
                'managerPath' => 'tools/resource_manager.yml',
            ],
            //unknown resource
            [
                'basePath' => __DIR__.'/../../../Stub/transfert/invalid',
                'isExceptionExpected' => true,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
                'managerPath' => 'tools/unknown_resources.yml',
            ],
            //parent don't exist
            [
                'basePath' => __DIR__.'/../../../Stub/transfert/invalid',
                'isExceptionExpected' => true,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
                'managerPath' => 'tools/missing_parent.yml',
            ],
            //parent element is missing
            [
                'basePath' => __DIR__.'/../../../Stub/transfert/invalid',
                'isExceptionExpected' => true,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
                'managerPath' => 'tools/missing_root.yml',
            ],
        ];
    }
}
