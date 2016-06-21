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
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\Tools\Resources\TextImporter;
use Symfony\Component\Yaml\Yaml;

class TextImporterTest extends MockeryTestCase
{
    private $fileImporter;

    protected function setUp()
    {
        parent::setUp();

        $this->fileImporter = new TextImporter();
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($basePath, $path, $isExceptionExpected)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $data = Yaml::parse(file_get_contents($basePath.'/'.$path));
        $this->fileImporter->setRootPath($basePath);
        $this->fileImporter->validate($data);
    }

    /* @todo add validations */
    public function validateProvider()
    {
        return array(
            //valid (the file path is correct)
            array(
                'basePath' => __DIR__.'/../../../../Stub/transfert/valid/full',
                'path' => 'tools/resources/text01.yml',
                'isExceptionExpected' => false,
            ),
        );
    }
}
