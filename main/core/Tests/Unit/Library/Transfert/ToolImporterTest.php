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
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\ToolsImporter;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Symfony\Component\Yaml\Yaml;
use Doctrine\Common\Collections\ArrayCollection;

class ToolImporterTest extends MockeryTestCase
{
    private $importer;
    private $importers;
    private $homeImporter;

    public function setUp()
    {
        parent::setUp();

        $this->importer = new ToolsImporter();
        $this->importers = new ArrayCollection();

        $this->homeImporter = $this->mock('Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\HomeImporter');
        $this->homeImporter->shouldReceive('getName')->andReturn('home');
        $this->homeImporter->shouldReceive('validate')->andReturn(true);
        $this->importers->add($this->homeImporter);
        $this->importer->setListImporters($this->importers);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($basePath, $configPath, $isExceptionExpected, $rolefile)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $roles = Yaml::parse(file_get_contents($rolefile));
        $resolver = new Resolver($basePath, $configPath);
        $data = $resolver->resolve();
        $this->importer->setConfiguration($roles);
        $this->importer->validate($data);
    }

    public function validateProvider()
    {
        return array(
            //correct
            array(
                'basePath' => __DIR__.'/../../../Stub/transfert/valid/full',
                'configPath' => 'tools.yml',
                'isExceptionExpected' => false,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
            //unkown role
            array(
                'basePath' => __DIR__.'/../../../Stub/transfert/valid/full',
                'configPath' => 'tools.yml',
                'isExceptionExpected' => true,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles02.yml',
            ),
            //unknown tool
            array(
                'basePath' => __DIR__.'/../../../Stub/transfert/invalid/tools',
                'configPath' => 'unknown_tool.yml',
                'isExceptionExpected' => true,
                'rolefile' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
            ),
        );
    }
}
