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
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\HomeImporter;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Doctrine\Common\Collections\ArrayCollection;

class HomeImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;
    private $importers;

    protected function setUp()
    {
        parent::setUp();

        $this->importer = new HomeImporter($this->om);

        $simpleTextImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Widgets\TextImporter');
        $simpleTextImporter->shouldReceive('getName')->andReturn('simple_text');
        $simpleTextImporter->shouldReceive('validate')->andReturn(true);

        $this->importers = new ArrayCollection();
        $this->importers->add($simpleTextImporter);

        $this->importer->setListImporters($this->importers);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($basePath, $configPath, $isExceptionExpected)
    {
        $this->importer->setRootPath($basePath);

        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $resolver = new Resolver($basePath, $configPath);
        $data = $resolver->resolve();
        $this->importer->validate($data);
    }

    public function validateProvider()
    {
        return array(
            //correct
            array(
                'basePath' => __DIR__.'/../../../Stub/transfert/valid/full',
                'configPath' => 'tools/home.yml',
                'isExceptionExpected' => false,
            ),
        );
    }
}
