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
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\RolesImporter;
use Symfony\Component\Yaml\Yaml;

class RolesImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->importer = new RolesImporter($this->om);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected)
    {
        if ($isExceptionExpected) {
            $this->setExpectedException('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException');
        }

        $data = Yaml::parse(file_get_contents($path));
        $roles['roles'] = $data['roles'];
        $this->importer->validate($roles);
    }

    public function validateProvider()
    {
        return [
            //valid
            [
                'path' => __DIR__.'/../../../Stub/transfert/valid/full/roles01.yml',
                'isExceptionExpected' => false,
            ],
            //roles have the same name twice
            [
                'path' => __DIR__.'/../../../Stub/transfert/invalid/roles/existing_name.yml',
                'isExceptionExpected' => true,
            ],
        ];
    }
}
