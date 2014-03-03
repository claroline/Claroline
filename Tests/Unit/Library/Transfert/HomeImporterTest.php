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
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\HomeImporter;
use Claroline\CoreBundle\Library\Transfert\Resolver;
use Symfony\Component\Yaml\Yaml;

class HomeImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->importer = new HomeImporter($this->om);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($basePath, $configPath, $isExceptionExpected)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $fullpath = $basePath . '/' . $configPath;
        $resolver = new Resolver($basePath, $configPath);
        $data = $resolver->resolve();
        $this->importer->setRootPath($basePath);
        $this->importer->validate($data);
    }

    public function validateProvider()
    {
        return array(
            //correct
            array(
                'basePath' => __DIR__.'/../../../Stub/transfert/valid/full',
                'configPath' => 'tools/home.yml',
                'isExceptionExpected' => false
            )
        );
    }
} 