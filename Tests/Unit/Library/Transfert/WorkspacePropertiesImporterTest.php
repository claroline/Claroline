<?php

namespace Claroline\CoreBundle\Library;

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Mockery as m;
use Claroline\CoreBundle\Library\Transfert\ConfigurationBuilders\WorkspacePropertiesImporter;
use Symfony\Component\Yaml\Yaml;

class WorkspacePropertiesImporterTest extends MockeryTestCase
{
    private $om;
    private $importer;

    protected function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->importer = new WorkspacePropertiesImporter($this->om);
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path, $isExceptionExpected, $isUserInDatabase, $isFull)
    {
        $ds = DIRECTORY_SEPARATOR;
        $data = Yaml::parse(file_get_contents($path . $ds . 'manifest.yml'));
        $properties['properties'] = $data['properties'];

        if ($isExceptionExpected) {
            $this->setExpectedException('Exception');
        }

        m::getConfiguration()->allowMockingNonExistentMethods(true);

        //init importer
        $this->importer->setRootPath($path);
        $this->importer->setManifest($data);
        //objectManager
        $wsRepo = $this->mock('Claroline\CoreBundle\Repository\WorkspaceRepository');
        $this->om->shouldReceive('getRepository')
            ->with('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace')
            ->andReturn($wsRepo);
        $wsRepo->shouldReceive('findOneByCode')->once()->with($properties['properties']['code']);

        $userRepo = $this->mock('Claroline\CoreBundle\Repository\UserRepository');

        if ($isUserInDatabase && !$isFull) {
            $this->om->shouldReceive('getRepository')->andReturn($userRepo);
            $userRepo->shouldReceive('findOneByUsername')->andReturn('user');
        }

        if (!$isUserInDatabase && !$isFull) {
            $this->om->shouldReceive('getRepository')->andReturn($userRepo);
            $userRepo->shouldReceive('findOneByUsername')->andThrow('Exception');
        }

        $this->importer->validate($properties);
    }

    public function validateProvider()
    {
        return array(
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => false,
                'isUserInDatabase' => false,
                'isFull' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/full',
                'isExceptionExpected' => false,
                'isUserInDatabase' => true,
                'isFull' => true
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/minimal',
                'isExceptionExpected' => false,
                'isUserInDatabase' => true,
                'isFull' => false
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/valid/minimal',
                'isExceptionExpected' => true,
                'isUserInDatabase' => false,
                'isFull' => false
            ),
            array(
                'path' => __DIR__.'/../../../Stub/transfert/invalid/wrong_owner',
                'isExceptionExpected' => true,
                'isUserInDatabase' => true,
                'isFull' => true
            )
        );
    }
}